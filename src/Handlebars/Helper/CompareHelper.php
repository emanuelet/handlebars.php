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
        $left = $this->getData($context, $arguments[0]);
        $operator = $arguments[1]['data'];
        $right = $this->getData($context, $arguments[2]);

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
    
    private function getData(Context $context, $arguments) {
        $data = $arguments['data']; 
        if ($arguments['type'] == 'normal') {
            if (is_numeric($data)) {
                $data += 0;
            } elseif (strtolower($data) == 'true') {
                $data = true;
            } elseif (strtolower($data) == 'false') {
                $data = false;
            } else {
                $data = $context->get($data);
            }
        }
         
        return $data;
    }
    
    private function split($args) {
        //replace \' with \"
        $args = str_replace("'", '"', $args);
        $chars = str_split($args);
        $mode = 'normal';
        $token = '';
        $tokens = [];
        for ($i = 0; $i < count($chars); $i++) {
            switch ($mode) {
                case 'normal':
                    if ('"' == $chars[$i]) {
                        if ('' != $token) {
                            $tokens[] = $token;
                        }
                        $token = '';
                        $mode = 'quoting';
                    } else if (' ' == $chars[$i] || "\t" == $chars[$i] || "\n" == $chars[$i]) {
                        if ('' != $token) {
                            $tokens[] = ['type' => 'normal', 'data' => $token];
                        }
                        $token = '';
                    } else {
                        $token .= $chars[$i];
                    }
                    break;

                case 'quoting':
                    if ('"' == $chars[$i]) {
                        if ('' != $token) {
                            $tokens[] = ['type' => 'quoting', 'data' => $token];
                        }
                        $token = '';
                        $mode = 'normal';
                    } else {
                        $token .= $chars[$i];
                    }
                    break;
            }
        }
        if ('' != $token) {
            $tokens[] = ['type' => 'normal', 'data' => $token];
        }
        if (empty($tokens) || sizeof($tokens) != 3 || $tokens[1]['type'] != 'quoting') {
            throw new \InvalidArgumentException('Arguments error for compare helper arguments (' . $args . ')');
        }

        return $tokens;
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
