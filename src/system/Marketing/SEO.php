<?php namespace Marketing;

class SEO extends \BaseModel {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'seo';

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
     * @param $inputs
     * @return SEO|null
     */
    public static function createOrUpdate( $inputs )
    {
        if(! isset($inputs['link_id'])) return null;

        if($seo = static::findByLink($inputs['link_id']))
        {
            $seo->update($inputs);

            return $seo;
        }
        else
        {
            return static::create($inputs);
        }
    }

    /**
     * @param $link_id
     * @return SEO
     */
    public static function findByLink( $link_id )
    {
        return static::where('link_id', $link_id)->first();
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function link()
    {
        return $this->belongsTo('Route\Link');
    }
}