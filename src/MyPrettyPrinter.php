<?php
//========================================================================
// Author:  Pascal KISSIAN
// Resume:  http://pascal.kissian.net
//
// Copyright (c) 2015-2017 Pascal KISSIAN
//
// Published under the MIT License
//          Consider it as a proof of concept!
//          No warranty of any kind.
//          Use and abuse at your own risks.
//========================================================================

namespace pmaslak\PhpObfuscator;
use PhpParser;

class MyPrettyPrinter extends PhpParser\PrettyPrinter\Standard
{
    private function obfuscate_string($str)
    {
        $l = strlen($str);
        $result = '';
        for ($i = 0; $i < $l; ++$i) {
            $result .= mt_rand(0, 1) ? "\x" . dechex(ord($str[$i])) : "\\" . decoct(ord($str[$i]));
        }
        return $result;
    }

    public function pScalar_String(PhpParser\Node\Scalar\String_ $node)
    {
        $result = $this->obfuscate_string($node->value);
        if (!strlen($result)) return "''";
        return '"' . $this->obfuscate_string($node->value) . '"';
    }

    //TODO: pseudo-obfuscate HEREDOC string
    public function pScalar_Encapsed(PhpParser\Node\Scalar\Encapsed $node)
    {
        $result = '';
        foreach ($node->parts as $element) {
            if ($element instanceof PhpParser\Node\Scalar\EncapsedStringPart) {
                $result .= $this->obfuscate_string($element->value);
            } else {
                $result .= '{' . $this->p($element) . '}';
            }
        }
        return '"' . $result . '"';
    }
}
