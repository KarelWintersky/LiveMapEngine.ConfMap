<?php

namespace Confmap\Controllers;

use Arris\AppRouter;
use Arris\Path;
use Arris\Template\Template;
use Confmap\AbstractClass;
use Confmap\App;
use Confmap\Units\Map;
use Psr\Log\LoggerInterface;

class RegionsController extends AbstractClass
{
    private Map $map;

    public function __construct($options = [], LoggerInterface $logger = null)
    {
        parent::__construct($options, $logger);
    }

    /**
     * Показ инфы по региону
     *
     * @return void
     * @throws \ColinODell\Json5\SyntaxError
     * @throws \JsonException
     * @throws \SmartyException
     */
    public function view_region_info()
    {
        $map_alias = App::$map_id;
        $id_region = $_GET['id']    ?? null;
        $template  = $_GET['resultType'] ?? 'html';

        $this->map = new Map($this->pdo, $map_alias);
        $this->map->loadConfig(
            Path::create( config('path.storage') )->join($map_alias)
        );

        $region_data = $this->map->getMapRegionData($id_region, [ 'title', 'content']);

        $t = new Template();
        $t
            ->setTemplateDir( config('smarty.path.template'))
            ->setCompileDir( config('smarty.path.cache'))
            ->setForceCompile( config('smarty.force_compile'))
            ->registerClass("Arris\AppRouter", "Arris\AppRouter");

        $t->assign('is_present', $region_data['is_present']);
        $t->assign('map_alias', $map_alias);
        $t->assign('region_id', $id_region);
        $t->assign('region_title', $region_data['title']);
        $t->assign('region_text', $region_data['content']);
        $t->assign('is_can_edit', $region_data['can_edit']);
        $t->assign('edit_button_url', AppRouter::getRouter('update.region.info'));

        switch ($template) {
            case 'iframe': {
                $t->setTemplate('view.region/view.region.iframe.tpl');;
                break;
            }
            case 'fieldset': {
                $t->setTemplate('view.region/view.region.fieldset.tpl');
                break;
            }
            default: {
                $t->setTemplate('view.region/view.region.html.tpl');
                break;
            }
        }
        $content = $t->render(false);

        $this->template->assignRAW($content);
    }

    public function view_region_edit_form()
    {
        $this->template->assign("html_callback", AppRouter::getRouter('view.frontpage'));
        $this->template->assign("form_actor", AppRouter::getRouter('update.region.info'));
        $this->template->setTemplate("_edit.region.tpl");
    }

    public function callback_update_region()
    {

    }

}