<?php
namespace Plugin\CategoryContext\ServiceProvider;

use Eccube\Application;
use Silex\Application as BaseApplication;
use Silex\ServiceProviderInterface;

class CategoryContextServiceProvider implements ServiceProviderInterface
{
    public function register(BaseApplication $app)
    {
    	//Repository
        $app['category_context.repository.category_context'] = $app->share(function () use ($app) {
            return $app['orm.em']->getRepository('Plugin\CategoryContext\Entity\CategoryContext');
        });
    }

    public function boot(BaseApplication $app)
    {
    }
}