<?php namespace Kareem3d\Routing;

use Helper\Helper;
use Kareem3d\Eloquent\Model;
use Kareem3d\Routing\Filter;
use Illuminate\Support\Facades\App;
use Kareem3d\URL\URL;

class Link extends Model {

    const DEFAULT_TYPE = 'get';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'links';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array();

	/**
	 * The attributes that can't be mass assigned
	 *
	 * @var array
	 */
    protected $guarded = array('id');

    /**
     * Whether or not to softDelete
     *
     * @var bool
     */
    protected $softDelete = false;

    /**
     * @var array
     */
    protected static $dontDuplicate = array('name');

    /**
     * Validations rules
     *
     * @var array
     */
    protected $rules = array(
    );

    /**
     * For factoryMuff package to be able to fill attributes.
     *
     * @var array
     */
    public static $factory = array(
    );

    /**
     * @var Link|null
     */
    protected static $currentLink = null;

    /**
     * @param array $attributes
     * @return \Kareem3d\Routing\Link
     */
    public static function createWithUrl( array $attributes )
    {
        $helper = Helper::instance();

        $urlAttrs = $helper->arrayGetKeys($attributes, array('uri', 'domain'));

        $linkAttrs = $helper->arrayGetKeys($attributes, array('name', 'parameters', 'controller', 'actions'));

        // Create new URL from the url attributes
        $url = URL::create($urlAttrs);

        // Set the url id of the link to the created url
        $linkAttrs['url_id'] = $url->id;

        // create and return the link
        return static::create($linkAttrs);
    }

    /**
     * @param $name
     * @return Link
     */
    public static function getByName( $name )
    {
        return static::where('name', $name)->first();
    }

    /**
     * @param $requestType
     * @return \Kareem3d\Routing\Link
     */
    public static function getCurrent( $requestType = '' )
    {
        if(! static::$currentLink && $currentUrl = URL::getCurrent())

            static::$currentLink = static::getByUrlAndRequestType( $currentUrl, $requestType );

        return static::$currentLink;
    }

    /**
     * @param URL $url
     * @param $type
     * @return \Kareem3d\Routing\Link|null
     */
    public static function getByUrlAndRequestType( URL $url , $type )
    {
        $link = static::getByUrl( $url );

        if($link && $link->hasRequestType( $type ))
        {
            // Set current action by request type
            $link->setCurrentAction( $type );

            return $link;
        }

        return null;
    }

    /**
     * @param URL $url
     * @return \Kareem3d\Routing\Link
     */
    public static function getByUrl( URL $url )
    {
        return static::where('url_id', $url->id)->first();
    }

    /**
     * @param $value
     * @return void
     */
    public function setActionsAttribute($value)
    {
        if(is_array($value))
        {
            $this->attributes['actions'] = serialize($value);
        }
        else
        {
            $this->attributes['actions'] = serialize(array(self::DEFAULT_TYPE => $value));
        }
    }

    /**
     * @param $value
     * @return array
     */
    public function getActionsAttribute($value)
    {
        // This should be serialized as an array but to be sure we will check for it
        $unserialized = @unserialize($value);

        if(is_array($unserialized))
        {
            return $unserialized;
        }

        // Set default type to get
        return array(self::DEFAULT_TYPE => $value);
    }

    /**
     * @return bool
     */
    public function controllerAndActionExists()
    {
        if($controller = App::make($this->getController())) return false;

        return method_exists($controller, $this->getCurrentAction());
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function hasParameters()
    {
        $parameters = $this->getParameters();

        return ! empty( $parameters );
    }

    /**
     * @return array
     */
    public function getParameters()
    {
        if(strpos($this->parameters, ',') > -1)

            return explode(',', $this->parameters);

        return (array) $this->parameters;
    }

    /**
     * @return mixed
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return $this->actions;
    }

    /**
     * @return mixed
     */
    public function getCurrentAction()
    {
        return $this->currentAction;
    }

    /**
     * @param $type
     */
    public function setCurrentAction( $type )
    {
        $this->attributes['currentAction'] = $this->getActionByRequestType($type);
    }

    /**
     * @param $type
     * @return string
     */
    public function getActionByRequestType( $type )
    {
        $type = strtolower($type);

        return isset($this->actions[ $type ]) ? $this->actions[ $type ] : null;
    }

    /**
     * @param $type
     * @return bool
     */
    public function hasRequestType( $type )
    {
        return $this->getActionByRequestType($type) != null;
    }

    /**
     * @return bool
     */
    public function hasBeforeFilters()
    {
        return $this->filters()->where('type', Filter::BEFORE)->count() > 0;
    }

    /**
     * @return bool
     */
    public function hasAfterFilters()
    {
        return $this->filters()->where('type', Filter::AFTER)->count() > 0;
    }

    /**
     * @return mixed
     */
    public function getBeforeFilters()
    {
        return $this->filters()->where('type', Filter::BEFORE)->get();
    }

    /**
     * @return mixed
     */
    public function getAfterFilters()
    {
        return $this->filters()->where('type', Filter::AFTER)->get();
    }

    /**
     * @return string
     */
    public function getConcatBeforeFilters()
    {
        return $this->concatFilters($this->getBeforeFilters());
    }

    /**
     * @return string
     */
    public function getConcatAfterFilters()
    {
        return $this->concatFilters($this->getAfterFilters());
    }

    /**
     * @param $filters
     * @return string
     */
    public function concatFilters( $filters )
    {
        $string = '';

        foreach($filters as $filter)
        {
            $string .= $filter->getName() . '|';
        }

        return rtrim($string, '|');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function filters()
    {
        return $this->belongsToMany('Kareem3d\Routing\Filter', 'link_filter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function url()
    {
        return $this->belongsTo('Kareem3d\URL\URL');
    }
}