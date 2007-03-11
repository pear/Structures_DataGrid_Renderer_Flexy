<?php
/* vim: set expandtab tabstop=4 shiftwidth=4: */

/**
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
 */

require_once 'Structures/DataGrid/Renderer.php';

/**
 * Flexy Rendering Driver
 *
 * SUPPORTED OPTIONS:
 * 
 * - selfPath:            (string) The complete path for sorting and paging links.
 *                                 (default: $_SERVER['PHP_SELF'])
 * - sortingResetsPaging: (bool)   Whether sorting HTTP queries reset paging.  
 *                                 (default: true)
 * - convertEntities:     (bool)   Whether or not to convert html entities.
 *                                 This calls htmlspecialchars(). 
 *                                 (default: true)
 * - pagerOptions:        (array)  The custom options to be sent to the Pager renderer.
 * - headerAttributes:    (array)  The settings specific for rendering the column header.
 *   - assocColumns:      (bool)   Whether or not to build the column header as 
 *                                 an associate array.
 *                                 (default: true)
 *   - formatter:         (array)  The callback array for a column header formatter method.
 *                                 (default: array($this,'defaultHeaderFormatter'))
 *   - columnNames:       (array)  The set of column names to use for the column header.
 *                                 (default: array)
 * - oddRowAttribute:     (string) The css class to be used for odd row listings.
 *                                 (default: odd)
 * - evenRowAttribute     (string) The css class to be used for the even row listings.
 *                                 (default: even)
 * - resultsFormat        (string) The format of the results message in sprintf format.
 *                                 (default: 'You have %s results in %s pages')
 * 
 * SUPPORTED OPERATION MODES:
 *
 * - Container Support: yes
 * - Output Buffering:  yes
 * - Direct Rendering:  no
 *
 * GENERAL NOTES:
 *
 * This driver does not support the render() method, it only is able to be attached
 * setting the container to a current Flexy instance. Options to the renderer must 
 * also be passed using the setOptions() method.
 *
 * Flexy output is buffered using the DataGrid getOutput() method.
 *
 * This driver assigns the following Flexy template variables: 
 * - columnSet:       array of columns' labels and sorting links
 * - columnHeader:    object of columns' labels and sorting links
 * - recordSet:       associate array of records values
 * - numberedSet:     numbered array of records values
 * - currentPage:     current page (starting from 1)
 * - recordLimit:     number of rows per page
 * - pagesNum:        number of pages
 * - columnsNum:      number of columns
 * - recordsNum:      number of records in the current page
 * - totalRecordsNum: total number of records
 * - firstRecord:     first record number (starting from 1)
 * - lastRecord:      last record number (starting from 1)
 * 
 * This driver also register a Smarty custom function named getPaging
 * that can be called from Smarty templates with {getPaging} in order
 * to print paging links. This function accepts any of the Pager::factory()
 * options as parameters.
 *
 * Dynamic Template example, featuring sorting and paging:
 * 
 * <code>
 * <!-- Show paging links using the custom getPaging function -->
 * {getPaging():h}
 * 
 * <p>Showing records {firstRecord} to {lastRecord} 
 * from {totalRecordsNum}, page {currentPage} of {pagesNum}</p>
 * 
 * <table cellspacing="0">
 *    <!-- Build header -->
 *    <tr>
 *        <th> 
 *            {foreach:columnSet,column}
 *                <td><a href="{column[link]:h}">{column[label]:h}</a></td>
 *            {end:}
 *        </th>
 *    </tr>
 *
 *    <!-- Build body -->
 *    <tr class="{getRowCSS()}" flexy:foreach="numberedSet,k,row">
 *        {foreach:row,field}
 *            <td>{field}</td>
 *        {end:}
 *    </tr>
 * </table>
 * </code>
 * 
 * Static Template example, featuring sorting and paging:
 * 
 * <code>
 * <table cellspacing="0">
 *    <!-- Build header -->
 *    <tr>
 *        <th> 
 *            <td>
 *                <a href="{columnHeader.name[link]:h}">{columnHeader.field1[label]:h}</a>
 *            </td>
 *            <td>
 *               <a href="{columnHeader.surname[link]:h}">{columnHeader.field2[label]:h}</a>
 *            </td>
 *        </th>
 *    </tr>
 *     
 *    <!-- Build body -->
 *    <tr class="{getRowCSS()}" flexy:foreach="recordSet,k,row">
 *        <td>{row[field1]}</td>
 *        <td>{row[field2]}</td>
 *    </tr>
 * </table>
 * </code>
 * 
 * <code>
 * require_once 'HTML/Template/Flexy.php';
 * require_once 'Structures/DataGrid.php';
 * require_once 'Structures/DataGrid/Renderer/Flexy.php';
 *
 * $tpl = new HTML_Template_Flexy($config['HTML_Template_Flexy']);
 * $dg =& new Structures_DataGrid($_GET['setPerPage'] ? $_GET['setPerPage'] 
 *                                                    : 10,$_GET['page'] 
 *                                                    ? $_GET['page'] : 1);
 * $dg->bind($dataObject);
 * $renderer = new Structures_DataGrid_Renderer_Flexy();
 * $renderer->setContainer($tpl);
 * $renderer->setOptions($config['Structures_DataGrid']);
 * $dg->attachRenderer($renderer);
 * $this->tpl->compile($template);
 * echo $dg->getOutput();
 * </code>
 *
 * @category   Structures
 * @package    Structures_DataGrid
 * @author     Andrew S. Nagy <asnagy@webitecture.org>
 * @author     Olivier Guilyardi <olivier@samalyse.com>
 * @author     Mark Wiesemann <wiesemann@php.net>
 * @author     Daniel Rossi <pear@electroteque.org>
 * @copyright  1997-2005 The PHP Group
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/Structures_DataGrid
 * @see        HTML_Template_Flexy, Pager
 * @access     public
 */
class Structures_DataGrid_Renderer_Flexy extends Structures_DataGrid_Renderer
{
    /**
     * Flexy container
     * @var object $_flexy;
     */
    var $_flexy;
    
    /**
     * Associate recordset array
     * @var array $_assocRecords;  
     */ 
    var $_assocRecords;
    
    /**
     * Column Header object
     * @var object $columnHeader; 
     */
    var $columnHeader;
    
    /**
     * Column Set array
     * @var array $columnSet; 
     */ 
    var $columnSet;
    
    /**
     * Constructor
     *
     * @access  public
     */
    function Structures_DataGrid_Renderer_Flexy()
    {
        parent::Structures_DataGrid_Renderer();
        $this->_addDefaultOptions(
            array(
                'selfPath'            => $_SERVER['PHP_SELF'],
                'convertEntities'     => true,
                'sortingResetsPaging' => true,
                'oddRowAttribute'     => 'odd',
                'evenRowAttribute'    => 'even',
                'resultsFormat'       => 'You have %s results in %s pages',
                'assocColumns'        => true,
                'formatter'           => array($this,'_defaultHeaderFormatter'),
                'columnNames'         => array(),
                //copied pager options from Pager renderer
                'pagerOptions' => array(
                    'mode'        => 'Sliding',
                    'delta'       => 5,
                    'separator'   => '|',
                    'prevImg'     => '<<',
                    'nextImg'     => '>>',
                    'totalItems'  => null, // dynamic; see init()
                    'perPage'     => null, // dynamic; see init()
                    'urlVar'      => null, // dynamic; see init()
                    'currentPage' => null, // dynamic; see init()
                    'extraVars'   => array(),
                    'excludeVars' => array(),
                )
            )
        );
        
        $this->_setFeatures(
            array(
                'outputBuffering' => true,
            )
        );
    }
    
    /**
     * Set multiple options
     *
     * @param   mixed   $options    An associative array of the form:
     *                              array("option_name" => "option_value",...)
     * @access  public
     */
    function setOptions($options)
    {
        /* This method is overloaded here because array_merge() needs to be called
         * over the "headerAttributes" option. Otherwise, if the user only provide a few
         * header options, built-in defaults generally get overwritten.
         *
         * setOptions() is a public method, so it can be overloaded. But, because
         * the $_options method is considered read-only, this method does not write 
         * into this property directly. It calls parent::setOptions() instead.
         */
         /*
        if (isset($options['headerAttributes'])) {
            $options['headerAttributes'] = array_merge($this->_options['headerAttributes'], 
                                                    $options['headerAttributes']);
        }
        */
        parent::setOptions($options);
    }
    
    /**
     * Attach an already instantiated Flexy object
     * 
     * @param   object   $flexy    Flexy object
     * @access  public
     * @return  bool   true
     */
    function setContainer(&$flexy)
    {
        $this->_flexy =& $flexy;
        return true;
    }
    
    /**
     * Return the currently used Flexy object
     *
     * @access  public
     * @return  object   Flexy or PEAR_Error object
     */
    function &getContainer()
    {
        if (!isset($this->_flexy)) {
            return PEAR::raiseError("no Flexy container loaded");
        }
        return $this->_flexy;
    }
    
    /**
     * Initialize the Flexy container
     * 
     * @access  protected
     * @return  void
     */
    function init()
    {
        if (!isset($this->_flexy)) {
            return PEAR::raiseError("no Flexy container loaded");
        }
        
        $this->currentPage = $this->_page;
        $this->recordLimit = $this->_pageLimit;
        $this->columnsNum = $this->_columnsNum;
        $this->recordsNum = $this->_recordsNum;
        $this->totalRecordsNum = $this->_totalRecordsNum;
        $this->pagesNum = $this->_pagesNum;
        $this->firstRecord = $this->_firstRecord;
        $this->lastRecord = $this->_lastRecord;
    }
    
    /**
     * Builds the column header
     * Determines if to build the columnSet array as associate or numbered.
     * Preformats the column header labels using the selected header formatter method.
     * Generates sorting links for the labels.
     * Cast the columnSet array to an object columnHeader for flexy template use.
     * 
     * @param   array    $columns     The array of column fields and labels.
     * @access  protected
     * @return  void
     */
    function buildHeader(&$columns)
    {
        $prepared = array();
      
        foreach ($columns as $index => $spec) {
            
            //use the field as an index for creating an associate column array
            $this->_options['assocColumns'] 
            ? $index = $spec['field'] : $index = $index;
            
            if (in_array($spec['field'], $this->_sortableFields)) {
                reset($this->_currentSort);
                if (list($currentField,$currentDirection) = each($this->_currentSort)
                    and $currentField == $spec['field']) {
                    if ($currentDirection == 'ASC') {
                        $direction = 'DESC';
                    } else {
                        $direction = 'ASC';
                    }
                } else {
                    $direction = 'ASC';
                }
                $extra = array ('page' => $this->_options['sortingResetsPaging'] 
                                          ? 1 : $this->_page);
                $query = $this->_buildSortingHttpQuery($spec['field'], 
                                                       $direction, true, $extra);
                $prepared[$index]['link'] = "{$this->_options['selfPath']}?$query";
            } else {
                $query = '';
                $prepared[$index]['link'] = "";
            }
            
            //format the header column label
            $prepared[$index]['label'] = $this->_options['columnNames'][$index] 
                                         ? $this->_options['columnNames'][$index] 
                                         : $this->_formatHeaderCell($spec['label']);
        }
        
        $this->columnSet = $prepared;
        $this->columnHeader = (object)$prepared;
    }

    /**
     * Handles building the body.
     * 
     * Re-iterates through the columns array to regenerate the dataset as an 
     * associate array $_assocRecords from a numbered array $_records, 
     * for ease of flexy template use. 
     *
     * @access  protected
     * @return  void
     */
    function buildBody()
    {
        $row = 0;
        
        //reformat numbered array to associate array.
        for ($rec = 0; $rec < $this->_recordsNum; $rec++) {
            $record = array();
            $col = 0;
            foreach ($this->_columns as $column) {
                $records[$column['field']] = $this->_records[$rec][$col];
                $col++;
            }
            $this->_assocRecords[$rec] = $records;
        }
        
        $this->numberedSet = $this->_records; 
        $this->recordSet = $this->_assocRecords;
    }
    
    /**
     * Pager renderer per-page select menu
     * 
     * Setup the pager renderer and send it the pagerOptions config.
     * This is only meant to be called from a flexy template, using the
     * expression: {getPerPageSelectBox():h}
     * FIXME: Is there a more efficient of doing this?
     * 
     * @access  public
     * @return  string    Returns the per-page select box generated by the Pager Renderer
     */
    function getPerPageSelectBox()
    {
        $driver =& Structures_DataGrid::loadDriver('Structures_DataGrid_Renderer_Pager'); 
        $driver->setupAs($this, $this->_options['pagerOptions']);      
        return $driver->getContainer()->getPerPageSelectBox();
    }
    
    /**
     * Flexy custom function "getPaging"
     *
     * Setup the pager renderer and send it the pagerOptions config.
     * This is only meant to be called from a flexy template, using the
     * expression: {getPaging():h}
     *
     * @access  public
     * @return  string    Paging HTML links
     */
    function getPaging()
    {
        // Load and get output from the Pager rendering driver
        $driver =& Structures_DataGrid::loadDriver('Structures_DataGrid_Renderer_Pager');        
        $driver->setupAs($this, $this->_options['pagerOptions']);
        return $driver->getOutput();
    }
    
    /**
     * Default Results Statistics
     * 
     * This is only meant to be called from a flexy template, using the
     * expression: {getResults():h}
     * 
     * There are three ways to use this either:
     *
     * For the default messsage or string format using the resultsFormat config option:
     * {getResults():h}
     * 
     * Sending the format string, but only require total records and total pages
     * {getResults(#You have %s results in %s pages#):h}
     * 
     * Sending the format string, but organising the results message
     * {getResults(#Showing records %s to %s from %s, page %s of %s#
     * ,firstRecord,lastRecord,totalRecordsNum,currentPage,pagesNum):h}
     * 
     * FIXME: Added check to match count of %s to number of arguments to prevent PHP
     * Too Few Arguments Error, as unable to use @. This might need something more
     * efficient.
     * 
     * @param   mixed     $format The format of the results message in sprintf format
     * @access  public 
     * @return  string    The results message or PEAR error on bad formatted string
     */
    function getResults($format = null)
    {
        $args = func_get_args();
        
        //get count of % in a format string to match against count of arguments
        preg_match_all("/%/", $format, $match);
        $count = count($match[0]);
        
        if ($args[1]) {
            unset($args[0]);
            if ($count == count($args)) {
                return vsprintf($format, $args);
            }
            return PEAR::raiseError('Incorrect String Format, ' .
            		  'try and match % to the number of inputs required');
        }
        
        return sprintf($format ? $format : $this->_options['resultsFormat'], 
               $this->_totalRecordsNum, 
               $this->pagesNum);
        if ($count == 2) { 
            return PEAR::raiseError('Incorrect String Format, ' .
            		  'try and match % to the number of inputs required');
        }
        return false;
    }
    
    /**
     * Default formatter for all cells
     * 
     * @param   string    Cell value 
     * @access  protected
     * @return  string    Formatted cell value
     */
    function defaultCellFormatter($value)
    {
        return $this->_options['convertEntities']
               ? htmlspecialchars($value, ENT_COMPAT, $this->_options['encoding'])
               : $value;
    }
    
    /**
     * Header cell formatter
     * 
     * @param   string   $value    Cell value
     * @access  protected 
     * @return  string   Formatted cell value
     */
    function _formatHeaderCell($value)
    {
        return call_user_func($this->_options['formatter'], $value);
    }
    
    /**
     * Default header formatter
     * 
     * @param   string   $value    Cell value
     * @access  protected 
     * @return  string   Formatted cell value
     */
    function _defaultHeaderFormatter($value)
    {
        return $value;
    }
    
    /**
     * Buffers the rendered datagrid
     * 
     * @access public
     * @return string The buffered rendered datagrid output.
     */
    function flatten()
    {
        return $this->_flexy->bufferedOutputObject($this);
    }
    
    /**
     * Switches the row css for displaying odd/even row colours.
     * 
     * FIXME: Is there a more efficient of doing this?
     * Gets the odd/even values from the config settings 
     * oddRowAttribute and evenRowAttribute.
     * 
     * @access public 
     * @return string the css class
     */
    function getRowCSS()
    {
        static $i = 0;
        $row_class = $this->_options['oddRowAttribute'];
        $i % 2 ? 0 : $row_class = $this->_options['evenRowAttribute'];
        $i++;
        return $row_class;
    }
}
?>