<?php
namespace Plugin\CategoryContext;

use Eccube\Event\EventArgs;

use Eccube\Application;
use Eccube\Common\Constant;
use Eccube\Entity\Category;

use Eccube\Event\TemplateEvent;
use Plugin\CategoryContext\Entity\CategoryContext;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;

/**
 * Class CategoryContextEvent
 * @package Plugin\CategoryContext
 * author : Hoa.Nguyen
 * test it gain comment
 */

class CategoryContextEvent
{
    const CATEGORY_CONTEXT_TEXTAREA_NAME = 'plg_category_content';
    /** @var \Eccube\Application $app */
    private $app;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function onAdminProductCategoryInit(EventArgs $event)
    {
        /** @var Category $target_category */
        $TargetCategory = $event->getArgument('TargetCategory');

        $id = $TargetCategory->getId();

        $CategoryContent = null;

        // IDの有無で登録か編集かを判断
        if ($id) {
            // カテゴリ編集時は初期値を取得
            $CategoryContent = $this->app['category_context.repository.category_context']->find($id);
        }

         // カテゴリ新規登録またはコンテンツが未登録の場合
        if (is_null($CategoryContent)) {
            $CategoryContent = new CategoryContext();
        }

        // フォームの追加
        /** @var FormInterface $builder */
        // FormBuildeの取得
        $builder = $event->getArgument('builder');
        // 項目の追加
        $builder->add(
            self::CATEGORY_CONTEXT_TEXTAREA_NAME,
            'textarea',
            array(
                'required' => false,
                'label' => false,
                'mapped' => false,
                'attr' => array(
                    'placeholder' => 'コンテンツを入力してください(HTMLタグ使用可)',
                ),
            )
        );

        // 初期値を設定
        $builder->get(self::CATEGORY_CONTEXT_TEXTAREA_NAME)->setData($CategoryContent->getContent());

    }
    public function onAdminProductCategoryEditComplete(EventArgs $event)
    {   
         /** @var Application $app */
        $app = $this->app;
        /** @var Category $target_category */
        $TargetCategory = $event->getArgument('TargetCategory');
        /** @var FormInterface $form */
        $form = $event->getArgument('form');

        // 現在のエンティティを取得
        $id = $TargetCategory->getId(); 
        // フォーム値のIDをもとに登録カテゴリ情報を取得
        if(isset($app['category_context.repository.category_context']))
            $CategoryContent = $app['category_context.repository.category_context']->find($id); 

        if (is_null($CategoryContent)) {
            $CategoryContent = new CategoryContext();
        }

        // エンティティを更新
        $CategoryContent
            ->setId($id)
            ->setContent($form[self::CATEGORY_CONTEXT_TEXTAREA_NAME]->getData());

        // DB更新
        $app['orm.em']->persist($CategoryContent);
        $app['orm.em']->flush($CategoryContent);
    }
    public function onRenderProductList(TemplateEvent  $event)
    {
         $parameters = $event->getParameters();

        // カテゴリIDがない場合、レンダリングしない
        if (is_null($parameters['Category'])) {
            return;
        }

        // 登録がない、もしくは空で登録されている場合、レンダリングをしない
        $Category = $parameters['Category'];
        $CategoryContent = $this->app['category_context.repository.category_context']
            ->find($Category->getId());
        if (is_null($CategoryContent) || $CategoryContent->getContent() == '') {
            return;
        }

        // twigコードにカテゴリコンテンツを挿入
        // $snipet = '<div class="row">'.$CategoryContent->getContent().'</div>';
        $snipet = '<div class="row">{{ CategoryContent.content }}</div>';
        $search = '<div id="result_info_box"';
        $replace = $snipet.$search;
        $source = str_replace($search, $replace, $event->getSource());
        $event->setSource($source);

        // twigパラメータにカテゴリコンテンツを追加
        $parameters['CategoryContent'] = $CategoryContent;//only for twig
        $event->setParameters($parameters);//only for twig
    }
}