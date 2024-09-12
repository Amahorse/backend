<?php

declare(strict_types=1);

namespace Elements\Tracking\Models;

use Kodelines\Abstract\Model;


/**
 * Class TrackingModel
 * 
 * This class represents the Tracking Model.
 */
class TrackingModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    public $table = 'tracking';


    /**
     * The validator rules for the tracking model.
     *
     * @var array
     */
    public $validator = [
        "oauth_tokens_jti" => ['required']
    ];




}

?>