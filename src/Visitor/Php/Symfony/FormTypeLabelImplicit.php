<?php

/*
 * This file is part of the PHP Translation package.
 *
 * (c) PHP Translation team <tobias.nyholm@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Translation\Extractor\Visitor\Php\Symfony;

use PhpParser\Node;
use PhpParser\NodeVisitor;

/**
 * @author Rein Baarsma <rein@solidwebcode.com>
 */
final class FormTypeLabelImplicit extends AbstractFormType implements NodeVisitor
{
    use FormTrait;

    /**
     * {@inheritdoc}
     */
    public function enterNode(Node $node): ?Node
    {
        if (!$this->isFormType($node)) {
            return null;
        }

        parent::enterNode($node);

        $domain = null;
        // use add() function and look at first argument and if that's a string
        if ($node instanceof Node\Expr\MethodCall
            && (!\is_object($node->name) || method_exists($node->name, '__toString'))
            && ('add' === (string) $node->name || 'create' === (string) $node->name)
            && array_key_exists(0, $node->args) && $node->args[0]->value instanceof Node\Scalar\String_) {
            $skipLabel = false;
            // Check if the form type is "hidden"
            if (\count($node->args) >= 2) {
                $type = $node->args[1]->value;
                if ($type instanceof Node\Scalar\String_ && 'Symfony\Component\Form\Extension\Core\Type\HiddenType' === $type->value
                    || $type instanceof Node\Expr\ClassConstFetch && 'HiddenType' === $type->class->name) {
                    $skipLabel = true;
                }
            }

            // now make sure we don't have 'label' in the array of options
            if (\count($node->args) >= 3) {
                if ($node->args[2]->value instanceof Node\Expr\Array_) {
                    foreach ($node->args[2]->value->items as $item) {
                        if (isset($item->key) && $item->key instanceof Node\Scalar\String_ && 'label' === $item->key->value) {
                            $skipLabel = true;
                        }

                        if (isset($item->key) && $item->key instanceof Node\Scalar\String_ && 'translation_domain' === $item->key->value) {
                            if ($item->value instanceof Node\Scalar\String_) {
                                $domain = $item->value->value;
                            } elseif ($item->value instanceof Node\Expr\ConstFetch && 'false' === $item->value->name->toString()) {
                                $domain = false;
                            }
                        }
                    }
                }
                /*
                 * Actually there's another case here.. if the 3rd argument is anything else, it could well be
                 * that label is set through a static array. This will not be a common use-case so yeah in this case
                 * it may be the translation is double.
                 */
            }

            // only if no custom label was found, proceed
            if (false === $skipLabel && false !== $domain) {
                /*
                 * Pass DocComment (if available) from first argument (name of Form field) allowing usage of Ignore
                 * annotation to disable implicit add; use case: when form options are generated by external method.
                 */
                if ($node->args[0]->getDocComment()) {
                    $node->setDocComment($node->args[0]->getDocComment());
                }

                $label = $node->args[0]->value->value;
                if (!empty($label)) {
                    $label = $this->humanize($label);
                    if (null !== $location = $this->getLocation($label, $node->getAttribute('startLine'), $node, ['domain' => $domain])) {
                        $this->lateCollect($location);
                    }
                }
            }
        }

        return null;
    }

    /**
     * @see Symfony\Component\Form\FormRenderer::humanize()
     */
    private function humanize(string $text): string
    {
        return ucfirst(strtolower(trim(preg_replace(['/([A-Z])/', '/[_\s]+/'], ['_$1', ' '], $text))));
    }
}
