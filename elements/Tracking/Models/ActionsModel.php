<?php

declare(strict_types=1);

namespace Elements\Tracking\Models;

use Kodelines\Abstract\Model;
use Elements\Tracking\Tracking;

class ActionsModel extends Model {

    public $table = 'tracking_actions';

    public $validator = [
        "id_tracking" => ['required'],
        "action" => ['required'],
        "type" => ['required']
    ];

    public function create(array $values = [], $reget = true):array {


        $values['id_tracking'] = Tracking::getCurrentId();

        //Se non c'è il tracking non vengono inserite azioni 
        if(empty($values['id_tracking'])) {
            return [];
        }

        
        //In questo caso il reget non è mai necessario essendo solo funzione di archiviazione
        return parent::create($values,false);
    }

}

?>