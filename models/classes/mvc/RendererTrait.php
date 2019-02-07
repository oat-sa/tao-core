<?php
/**
 * Created by PhpStorm.
 * User: siwane
 * Date: 05/02/19
 * Time: 22:09
 */

namespace oat\tao\model\mvc;

use Renderer;

trait RendererTrait
{
    /**
     * @var Renderer
     */
    protected $renderer;

    public function getRenderer()
    {
        if (!isset($this->renderer)) {
            $this->renderer = new Renderer();
        }
        return $this->renderer;
    }

    public function setView($identifier)
    {
        $this->getRenderer()->setTemplate($identifier);
        return $this;
    }

    public function setData($key, $value)
    {
        $this->getRenderer()->setData($key, $value);
        return $this;
    }

    public function hasView()
    {
        return isset($this->renderer) && $this->renderer->hasTemplate();
    }
}