<?php

namespace App;

class Admin extends \Folk\Admin
{
    public $title = 'CMS';
    public $description = 'Content of the website';

    public function __construct($url, App $app)
    {
        parent::__construct($url);

        $this['url'] = $app->getUrl();
        $this['app'] = $app;

        if (env('APP_ADMIN_AUTH_USERNAME')) {
            $this['users'] = [
                env('APP_ADMIN_AUTH_USERNAME') => env('APP_ADMIN_AUTH_PASSWORD'),
            ];
        }

        $this->setEntities([
            //place your entities here
        ]);
    }
}
