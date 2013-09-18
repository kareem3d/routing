<?php namespace Routing;

use Routing\Filter;
use PathManager\Path;

class Link extends \BaseModel {

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
     * @param $value
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
     * @param $inputs
     * @return \Core\Link\Link
     */
    public static function createOrUpdate( $inputs )
    {
        if(isset($inputs['name']))
        {
            $link = static::findByName( $inputs['name'] );

            if($link)
            {
                $link->update($inputs);

                return $link;
            }
        }

        return static::create($inputs);
    }

    /**
     * @param $name
     * @return Link
     */
    public static function findByName( $name )
    {
        return static::where('name', $name)->first();
    }

    /**
     * @param $uri
     * @return \Core\Link\Link
     */
    public static function findByUri( $uri )
    {
        return static::where('uri', $uri)->first();
    }

    /**
     * @param $uri
     * @param $type
     * @return \Core\Link\Link|null
     */
    public static function findByUriAndRequestType( $uri, $type )
    {
        // First get link by uri
        $link = static::findByUri($uri);

        // Check to see if it has the given request type
        if($link && $link->hasRequestType( $type ))
        {
            // Set current action by request type
            $link->setCurrentAction( $type );

            return $link;
        }

        return null;
    }

    /**
     * @return bool
     */
    public function controllerAndActionExists()
    {
        if($controller = App::make($this->getController())) return false;

        foreach($this->getActions() as $action)
        {
            if(! method_exists($controller, $action))
            {
                return false;
            }
        }

        return true;
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
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return Path::getBaseUrl() . '/' . $this->getUri();
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function seo()
    {
        return $this->hasOne('Marketing\SEO\SEO');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function filters()
    {
        return $this->belongsToMany('Routing\Filter', 'link_filter');
    }
}