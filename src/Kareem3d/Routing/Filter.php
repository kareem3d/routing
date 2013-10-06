<?php namespace Kareem3d\Routing;

use Kareem3d\Eloquent\Model;
use Kareem3d\Code\Code;

class Filter extends Model {

    const BEFORE = 'before';
    const AFTER = 'after';

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'filters';

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
     * @param array $inputs
     * @return Filter
     */
    public static function createWithCode( array $inputs )
    {
        $code = Code::create(array('code' => $inputs['code']));

        $filter = static::create(array('title' => $inputs['title'], 'code_id' => $code->id));

        return $filter;
    }

    /**
     * @param Code $code
     */
    public function setCode( Code $code )
    {
        $this->code()->delete();

        $this->code()->associate($code);

        $this->save();
    }

    /**
     * @param $title
     * @return Filter
     */
    public static function getByTitle($title)
    {
        // An old trick to ignore case
        return static::where(DB::raw('UPPER(title)'), strtoupper($title))->first();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'filter' . $this->id;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function links()
    {
        return $this->belongsToMany('Kareem3d\Routing\Link', 'link_filter');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function code()
    {
        return $this->belongsTo('Kareem3d\Code\Code');
    }
}