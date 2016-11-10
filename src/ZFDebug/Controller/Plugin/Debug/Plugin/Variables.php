<?php
namespace ZFDebug\Controller\Plugin\Debug\Plugin;

use ZFDebug\Controller\Plugin\Debug\Plugin;

/**
 * Class Variables
 *
 * @package ZFDebug\Controller\Plugin\Debug\Plugin
 * @author  Octavian Matei <octav@octav.name>
 * @since   10.11.2016
 */
class Variables extends Plugin implements PluginInterface
{
    /**
     * Contains plugin identifier name
     *
     * @var string
     */
    protected $identifier = 'variables';

    /** @var \Zend_Controller_Request_Abstract */
    protected $request;

    /**
     * Gets identifier for this plugin
     *
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the base64 encoded icon
     *
     * @return string
     **/
    public function getIconData()
    {
        return 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAQAAAC1+jfqAAAABGdBTUEAAK/INwWK6QAAABl0RVh0U29mdHdhcmUAQWRvYmUgSW1hZ2VSZWFkeXHJZTwAAAFWSURBVBgZBcE/SFQBAAfg792dppJeEhjZn80MChpqdQ2iscmlscGi1nBPaGkviKKhONSpvSGHcCrBiDDjEhOC0I68sjvf+/V9RQCsLHRu7k0yvtN8MTMPICJieaLVS5IkafVeTkZEFLGy0JndO6vWNGVafPJVh2p8q/lqZl60DpIkaWcpa1nLYtpJkqR1EPVLz+pX4rj47FDbD2NKJ1U+6jTeTRdL/YuNrkLdhhuAZVP6ukqbh7V0TzmtadSEDZXKhhMG7ekZl24jGDLgtwEd6+jbdWAAEY0gKsPO+KPy01+jGgqlUjTK4ZroK/UVKoeOgJ5CpRyq5e2qjhF1laAS8c+Ymk1ZrVXXt2+9+fJBYUwDpZ4RR7Wtf9u9m2tF8Hwi9zJ3/tg5pW2FHVv7eZJHd75TBPD0QuYze7n4Zdv+ch7cfg8UAcDjq7mfwTycew1AEQAAAMB/0x+5JQ3zQMYAAAAASUVORK5CYII=';
    }

    /**
     * Gets menu tab for the Debug Bar
     *
     * @return string
     */
    public function getTab()
    {
        return ' Variables';
    }

    /**
     * Gets content panel for the Debug Bar
     *
     * @return string
     */
    public function getPanel()
    {
        $this->request = \Zend_Controller_Front::getInstance()->getRequest();

        $viewRenderer = \Zend_Controller_Action_HelperBroker::getStaticHelper('viewRenderer');
        if ($viewRenderer->view && method_exists($viewRenderer->view, 'getVars')) {
            $viewVars = $this->cleanData($viewRenderer->view->getVars());
        } else {
            $viewVars = "No 'getVars()' method in view class";
        }

        $vars = '<div style="width:50%;float:left;">';
        $vars .= '<h4>View variables</h4>' . '<div id="ZFDebug_vars" style="margin-left:-22px">' . $viewVars . '</div>';
        $vars .= '</div><div style="width:45%;float:left;">';
        $vars .= '<h4>Request parameters</h4>' . '<div id="ZFDebug_requests" style="margin-left:-22px">' . $this->cleanData($this->request->getParams()) . '</div>';
        if ($this->request->isPost()) {
            $vars .= '<h4>Post variables</h4>' . '<div id="ZFDebug_post" style="margin-left:-22px">' . $this->cleanData($this->request->getPost()) . '</div>';
        }

        $vars .= '<h4>Constants</h4>';
        $constants = get_defined_constants(true);
        ksort($constants['user']);
        $vars .= '<div id="ZFDebug_constants" style="margin-left:-22px">' . $this->cleanData($constants['user']) . '</div>';

        $registry = \Zend_Registry::getInstance();
        $vars .= '<h4>Zend Registry</h4>';
        $registry->ksort();
        $vars .= '<div id="ZFDebug_registry" style="margin-left:-22px">' . $this->cleanData($registry) . '</div>';

        $cookies = $this->request->getCookie();
        $vars .= '<h4>Cookies</h4>' . '<div id="ZFDebug_cookie" style="margin-left:-22px">' . $this->cleanData($cookies) . '</div>';

        $vars .= '</div><div style="clear:both">&nbsp;</div>';

        return $vars;
    }
}