<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
 * Basic examples for Structures/DataGrid/Renderer/Flexy
 * 
 * PHP version 4 
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 * 
 * @category   Structures
 * @package    Structures_DataGrid
 * @author     Andrew Nagy <asnagy@webitecture.org>  
 * @author     Olivier Guilyardi <olivier@samalyse.com>
 * @author     Mark Wiesemann <wiesemann@php.net>
 * @author     Dan Rossi <pear@electroteque.org>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id$
 * @link       http://pear.php.net/package/Structures_DataGrid
 * @see        HTML_Template_Flexy, Pager
 * @filesource
 */
     
require_once 'HTML/Template/Flexy.php';
require_once 'Structures/DataGrid.php';
require_once 'Structures/DataGrid/Renderer/Flexy.php';


class FlexyRenderer
{

    function FlexyRenderer($config)
    {    
        //Setup flexy object and read in config
        $this->tpl = new HTML_Template_Flexy($config['HTML_Template_Flexy']);
        $this->_config = $config;
    }
     
    function DG($do, $template, $limit = 10)
    {
        
        switch ($_GET['show'])
        {
            //show template source
            case 'template_source':
                echo file_get_contents('./templates/'.$template);
                exit;
            break;
        }
        
        /*
         * Setup the DataGrid with a flexy renderer
         */
         
        $dg =& new Structures_DataGrid($_GET['setPerPage'] ? $_GET['setPerPage'] : $limit,$_GET['page'] ? $_GET['page'] : 1);
        $dg->bind($do);
        $renderer = new Structures_DataGrid_Renderer_Flexy();
        $renderer->setContainer($this->tpl);
        
        //send options to the renderer
        $renderer->setOptions($this->_config['Structures_DataGrid']);
        $dg->attachRenderer($renderer);
        $this->tpl->compile($template);
        //get renderer output
        $this->datagrid = $dg->getOutput();
        
        //compile main template
        $this->tpl->compile('main.html');
        $this->tpl->output($this);
        exit;
     }
    
    /**
     * Default Header Formatter replaces underscores with spaces
     */
     
    function defaultHeaderFormatter($value)
    {
        return ucwords(preg_replace("/_/"," ",$value));
    }
    
    function noHeaderFormatter($value)
    {
        return $value;
    }
    
    function DS()
    {
        return array(
                    array('name'=>'My Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2'),
                    array('name'=>'Test Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2'),
                    array('name'=>'My Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2'),
                    array('name'=>'My Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2'),
                    array('name'=>'My Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2'),
                    array('name'=>'My Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2'),
                    array('name'=>'My Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2'),
                    array('name'=>'My Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2'),
                    array('name'=>'My Name','surname'=>'My Surname'),
                    array('name'=>'My Name2','surname'=>'My Surname2')
                    );
     }

    function display_static()
    {
        $this->DG($this->DS(), 'static_list.html');
    }
    
    function display_dynamic()
    {
            $this->DG($this->DS(), 'dynamic_list.html');
    }
        
    function display_header_formatter()
    {    
        $this->_config['Structures_DataGrid']['formatter'] = array($this,'defaultHeaderFormatter');
        $this->DG($this->DS(), 'static_list.html');
    }
    
    function display_custom_column_labels()
    {
        $this->_config['Structures_DataGrid']['columnNames'] = array('name'=>'Name Custom','surname'=>'Surname Custom');
        $this->DG($this->DS(), 'static_list.html');
    }
    
    function display_custom_results()
    {
        $this->DG($this->DS(), 'custom_results_list.html');
    }
    
    function display_formatted_results_default()
    {
        $this->DG($this->DS(), 'custom_results_default_list.html');
    }
    
    function display_formatted_results_no_arguments()
    {
        $this->DG($this->DS(), 'custom_results_noargs_list.html');
    }
    
    function display_formatted_results_arguments()
    {
        $this->DG($this->DS(), 'custom_results_args_list.html');
    }
    
    function run()
    {
        if ($_GET['example']) {
            if (method_exists($this, strtolower($_GET['example']))) call_user_func(array($this, strtolower($_GET['example'])));
        } else {
            $methods = get_class_methods($this);
            foreach($methods as $method)
            {
                if (preg_match("/display_/", $method))
                {    
                   echo "<br><a href=\"?example=$method\">".ucwords(preg_replace("/_/", " ", $method))."</a> &nbsp; &nbsp; <a href=\"?example=$method&show=template_source\">Template Source</a><br>";
                }
            }
            echo "<br><a href=\"index.phps\">Example Source</a> &nbsp; &nbsp; <a href=\"Flexy.phps\">Renderer Source</a>";
        }
    }
}

$config = array(
                'HTML_Template_Flexy'=> array(
                    'templateDir'        => './templates',
                    'compileDir'         => './templates_c',
                    'debug'              => false,
                    'globals'            => true,
                    'globalfunctions'    => true,
                    'allowPHP'           => true,
                    'privates'           => true,
                    'compiler'           => 'Flexy'
                ),
                'Structures_DataGrid' => array(
                    // Cutom Pager options using image icons for navigation
                    'pagerOptions'=> array(
                        'prevImg'=>"<img name=\"Back$i\" src=\"images/nav_back_off.gif\" border=\"0\" alt=\"Previous Page\" onmouseover=\"this.src='images/nav_back_on.gif'\" onmouseout=\"this.src='images/nav_back_off.gif'\">",
                        'nextImg'=>"<img name=\"Forward$i\" src=\"images/nav_forward_off.gif\" border=\"0\" alt=\"Next Page\" onmouseover=\"this.src='images/nav_forward_on.gif'\" onmouseout=\"this.src='images/nav_forward_off.gif'\">",
                        'firstPageText'=>"<img name=\"First$i\" src=\"images/nav_first_off.gif\" border=\"0\" alt=\"First Page\" onmouseover=\"this.src='images/nav_first_on.gif'\" onmouseout=\"this.src='images/nav_first_off.gif'\">",
                        'lastPageText'=>"<img name=\"Last$i\" src=\"images/nav_last_off.gif\" border=\"0\" alt=\"Last Page\" onmouseover=\"this.src='images/nav_last_on.gif'\" onmouseout=\"this.src='images/nav_last_off.gif'\">",
                        'firstPagePre'=>'',
                        'firstPagePost'=>'',
                        'lastPagePre'=> '',
                        'lastPagePost'=> ''
                      ),
                      'assocColumns' => true
                )
        );
        
$renderer = new FlexyRenderer($config);
$renderer->run();
?>