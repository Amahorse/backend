<?php


declare(strict_types=1);

namespace Elements\Users\Commands;

use Kodelines\Db;
use Kodelines\Abstract\Command;

class TokenCommand extends Command
{

    //TODO: questo va regolato e capito meglio come gestirlo, provare a mettere stored procedure direttamente dentro al db
    public function clear() 
    {
       Db::query(" DELETE FROM oauth_tokens WHERE jti NOT IN (SELECT oauth_tokens.jti FROM oauth_tokens JOIN store_orders ON oauth_tokens.jti = store_orders.oauth_tokens_jti) AND DATE(oauth_tokens.date_update) <> DATE(NOW()) AND id_users IS NULL");
    }




}