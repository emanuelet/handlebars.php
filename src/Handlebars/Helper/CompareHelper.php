<?php

namespace Handlebars\Helper;

use Handlebars\Context;
use Handlebars\Helper;
use Handlebars\Template;

class CompareHelper implements Helper
{
    /**
     * Execute the helper
     *
     * @param \Handlebars\Template $template The template instance
     * @param \Handlebars\Context  $context  The current context
     * @param array                $args     The arguments passed the the helper
     * @param string               $source   The source
     *
     * @return mixed
     */
    public function execute(Template $template, Context $context, $args, $source)
    {
        $arguments = $this->split($args);
        $left = $context->get($arguments[0]);
        $operator = $arguments[1];
        $right = $context->get($arguments[2]);

        if ($this->compare($left, $operator, $right)) {
            $template->setStopToken('else');
            $buffer = $template->render($context);
            $template->setStopToken(false);
            $template->discard($context);
        } else {
            $template->setStopToken('else');
            $template->discard($context);
            $template->setStopToken(false);
            $buffer = $template->render($context);
        }
        return $buffer;
    }
    
    private function split($args) {
        //replace \' with \"
        $args = str_replace("'", '"', $args);
        $arguments = explode('"', $args);
        if (empty($arguments) || sizeof($arguments) != 3) {
            throw new \InvalidArgumentException("Arguments error for compare helper!");
        }
        
        return $arguments;
    }
    
    private function compare($left, $operator, $right) {
        switch($operator) {
            case '==':
                $result = ($left == $right);
                break;
            case '===':
                $result = ($left === $right);
                break;
            case '!=':
                $result = ($left != $right);
                break;
            case '!==':
                $result = ($left !== $right);
                break;
            case '<':
                $result = ($left < $right);
                break;
            case '>':
                $result = ($left > $right);
                break;
            case '<=':
                $result = ($left <= $right);
                break;
            case '>=':
                $result = ($left >= $right);
                break;
            case 'typeof':
                $result = (gettype($left) == $right);
                break;
            default:
                throw new \Exception('Handlebars Helper "compare" doesn\'t know the operator ' . $operator);
        }
        
        return $result;
    }
}
