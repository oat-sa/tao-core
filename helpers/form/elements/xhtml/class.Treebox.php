<?php
use oat\taoBackOffice\model\tree\TreeService;

/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2008-2010 (original work) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *
 * Copyright (c) 2015 (original work) Open Assessment Technologies SA;
 *
 * @author Mikhail Kamarouski, <kamarouski@1pt.com>
 * @package tao
 */
class tao_helpers_form_elements_xhtml_Treebox extends tao_helpers_form_elements_Treebox
{
    /**
     * @var core_kernel_classes_Class
     */
    protected $range;

    /**
     * @return void
     */
    public function feed()
    {

        $expression = "/^" . preg_quote( $this->name, "/" ) . "(.)*[0-9]+$/";
        $found      = false;
        foreach ($_POST as $key => $value) {
            if (preg_match( $expression, $key )) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $this->setValues( array() );
            foreach ($_POST as $key => $value) {
                if (preg_match( $expression, $key )) {
                    $this->addValue( tao_helpers_Uri::decode( $value ) );
                }
            }
        }

    }

    /**
     * @access public
     *
     * @return array
     */
    public function getOptions()
    {
        return parent::getOptions();
    }

    /**
     * @param string $value
     *
     * @return void
     */
    public function setValue( $value )
    {
        $this->addValue( $value );
    }

    /**
     * @return string
     */
    public function render()
    {
        $widgetTreeBoxName     = $this->name . '-TreeBox';
        $widgetTreeStorageName = $this->name . '-TreeStorage';

        $returnValue = "<label class='form_desc' for='{$this->name}'>" . _dh( $this->getDescription() ) . "</label>";
        if ($this->getRange()->isSubClassOf( TreeService::singleton()->getRootClass() )) {

            $returnValue .= "<div class='form-elt-container' style='min-height:50px; overflow-y:auto;'>";

            $returnValue .= "<div id='{$widgetTreeStorageName}' >";
            $storeElement = \tao_helpers_form_FormFactory::getElement( $widgetTreeStorageName, 'Checkbox' );
            $storeElement->setOptions( $this->getOptions() );
            $storeElement->setValues( $this->getValues() );
            $returnValue .= $storeElement->render();
            $returnValue .= "</div>";

            $returnValue .= "<div id='{$widgetTreeBoxName}' class='tree-box'></div>";
            $returnValue .= "<style type='text/css'>
                #$widgetTreeStorageName{
                    display:none;
                }
                 </style>";

            $returnValue .= "<script type=\"text/javascript\" class='asd'>
    $(function () {

        require(['jquery', 'helpers', 'taoBackOffice/treeRender', 'uri'], function ($, helpers, treeRender, uriHelper) {
            'use strict';
            var container = $('#$widgetTreeBoxName');
            var dataContainer = $('#$widgetTreeStorageName');

            var uri = '{$this->getRange()->getUri()}';

            $.post(helpers._url('getTree', 'Trees', 'taoBackOffice'), {uri: uri}, function (treeData) {
                        var parent = container.closest('.tree-box');
                        var originalWidth, originalHeight;

                        var resizeContainer = function () {
                            container.height(parent.height() - parent.find('.panel').eq(0).outerHeight());
                            container.width(parent.width());
                        };

                        $(window).on('resize', resizeContainer);

                        resizeContainer();
                        var options = {
                            interaction: {
                                multiselect: true
                            }
                        };
                        var network = treeRender.run(container[0], treeData, options);

                        try {
                            network.selectNodes({$this->getSelectedNodes()});
                            network.fit({nodes: {$this->getSelectedNodes()}});
                        }
                        catch (e) {
                            if (e instanceof RangeError) {
                                // handle RangeError that thrown if node was not found ( f.e.base tree changed/replaced )
                            }
                        }
                        network.on('click', function (params) {
                            dataContainer.find(':checkbox').prop('checked', false);
                            $.each(params.nodes, function () {
                                dataContainer.find(':checkbox[value=' + uriHelper.encode(this) + ']').prop('checked', true);
                            });
                        });

                        container.hover(function () {
                            originalWidth = container.closest('.form-content').width();
                            originalHeight = container.height();

                            container.stop().animate({height: '50vh'}, '10', function () {
                                        network.redraw();
                                        network.fit({nodes: network.getSelectedNodes()})
                                    }
                            );

                            container.closest('.form-content').animate({width: '60vw'}, '10');
                            container.animate({width: '60vw'}, '10', function () {
                                network.redraw();
                                network.fit();
                            });

                        }, function () {
                            container.stop().animate({height: originalHeight}, '10', function () {
                                        network.redraw();
                                        network.fit();
                                    }
                            );
                            container.closest('.form-content').animate({width: originalWidth}, '10');
                            container.animate({width: originalWidth}, '10', function () {
                                network.redraw();
                                network.fit();
                            });

                        });
                    }
            )
            ;
        });
    })
    ;
</script>";


            $returnValue .= "</div><br />";

        } else {
            $returnValue .= __( 'Impossible to load tree resource' );
        }

        return $returnValue;
    }

    /**
     * @access public
     * @return mixed
     */
    public function getEvaluatedValue()
    {

        $values = array_map( "tao_helpers_Uri::decode", $this->getValues() );
        if (count( $values ) === 1) {
            return $values[0];
        } else {
            return $values;
        }

    }

    /**
     * @param core_kernel_classes_Class $range
     */
    public function setRange( core_kernel_classes_Class $range )
    {
        $this->range = $range;
    }

    /**
     * @return core_kernel_classes_Class
     */
    public function getRange()
    {
        return $this->range;
    }

    /**
     * Prepares well encoded list of selected nodes for embedding to JS
     * @return string
     */
    protected function getSelectedNodes()
    {
        return json_encode(
            array_map(
                function ( $uri ) {
                    return tao_helpers_Uri::decode( $uri );
                },
                $this->getValues()
            )
        );

    }

}

