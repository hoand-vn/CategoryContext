<?php
namespace Plugin\CategoryContext\Tests\Web;
use Eccube\Tests\Web\Admin\AbstractAdminWebTestCase;

/**
 * Class test of controller
 */
class CategoryContextControllerTest extends AbstractAdminWebTestCase
{
    public function setUp()
    {
        parent::setUp();

    }

    /**
     * Test routing
     *
     */
    public function testRoutingAdminProductCategoryContext()
    {
        $this->client->request('GET',
            $this->app->url('admin_product_category')
        );
        $this->assertTrue($this->client->getResponse()->isSuccessful());
    }

    /**
     * Create data
     *
     */
    public function createFormData()
    {
        $faker = $this->getFaker();
        $form = array(
            '_token' => 'dummy',
            'name' => $faker->randomNumber(1),
            'plg_category_content' =>'plg_category_content'
        );
        return $form;
    }
    /**
     * Test edit
     *
     */
    public function testCreateCategoryContext()
    {
        $formData = $this->createFormData();
        $this->client->request(
            'POST',
            $this->app->url('admin_product_category'),
            array('admin_category' => $formData)
        );
        $LowStockAlert = $this->app['category_context.repository.category_context']->findOneBy(array('content'=>$formData['plg_category_content']));
        $this->expected = $formData['plg_category_content'];
        $this->actual = $LowStockAlert->getContent();
        $this->verify();
    }
}