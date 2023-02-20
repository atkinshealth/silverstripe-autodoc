<?php

namespace AtkinsHealth\AutoDoc;

use Codeshift\AbstractCodemod;
use PhpParser\Comment;
use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;

if (!class_exists(AbstractCodemod::class)) {
    return;
}

if (!class_exists(Codemod::class)) {
    class Codemod extends AbstractCodemod
    {
        public function init()
        {
            parent::init();

            $visitor = new class extends NodeVisitorAbstract {
                public $namespace;

                public function enterNode(Node $node)
                {
                    if ($node instanceof Node\Stmt\Namespace_ && $node->name) {
                        $this->namespace = $node->name->toString();
                    }
                }

                public function leaveNode(Node $node)
                {
                    if ($node instanceof Node\Stmt\Class_) {
                        $className = $this->namespace . '\\' . $node->name;
                        if (
                            !is_subclass_of($className, DataObject::class, true)
                        ) {
                            return $node;
                        }
                        $shouldReplace = false;
                        $comment = '/**';
                        if ($comments = $node->getAttribute('comments')) {
                            foreach ($comments as $c) {
                                if ($c instanceof Doc) {
                                    $lines = explode("\n", $c->getText());
                                    $lines = preg_grep(
                                        '/@property|@method|\\/\\*|\\*\\//',
                                        $lines,
                                        PREG_GREP_INVERT
                                    );
                                    $comment =
                                        $comment . "\n" . implode("\n", $lines);
                                } elseif ($c instanceof Comment) {
                                    $comment =
                                        "/**\n" .
                                        implode(
                                            "\n",
                                            array_map(function ($l) {
                                                if (
                                                    !preg_match('/^ \\*/', $l)
                                                ) {
                                                    return " * $l";
                                                }
                                                return $l;
                                            }, explode(
                                                "\n",
                                                preg_replace(
                                                    '/\\*\\//',
                                                    '',
                                                    substr($c->getText(), 3)
                                                )
                                            ))
                                        );
                                }
                            }
                        }
                        $db = Config::inst()->get(
                            $className,
                            'db',
                            Config::UNINHERITED | Config::EXCLUDE_EXTRA_SOURCES
                        );
                        if ($db) {
                            foreach ($db as $f => $type) {
                                $shouldReplace = true;
                                $t = 'string';
                                if (preg_match('/int.*/i', $type)) {
                                    $t = 'int';
                                } elseif (
                                    preg_match('/(currency|number).*/i', $type)
                                ) {
                                    $t = 'float';
                                } elseif (preg_match('/bool.*/i', $type)) {
                                    $t = 'bool';
                                }
                                $comment .= "\n * @property $t $f";
                            }
                        }
                        $hasOne = Config::inst()->get(
                            $className,
                            'has_one',
                            Config::UNINHERITED | Config::EXCLUDE_EXTRA_SOURCES
                        );
                        if ($hasOne) {
                            foreach ($hasOne as $name => $desc) {
                                $shouldReplace = true;
                                $exp = explode('\\', $desc);
                                $class = array_pop($exp);
                                $comment .= "\n * @method $class $name()";
                                $comment .= "\n * @property int {$name}ID";

                                if ($desc == DataObject::class) {
                                    $comment .= "\n * @property string {$name}Class";
                                }
                            }
                        }
                        $hasMany = Config::inst()->get(
                            $className,
                            'has_many',
                            Config::UNINHERITED | Config::EXCLUDE_EXTRA_SOURCES
                        );
                        if ($hasMany) {
                            foreach ($hasMany as $name => $desc) {
                                $shouldReplace = true;
                                $exp = explode('\\', $desc);
                                $class = array_pop($exp);
                                $comment .= "\n * @method \SilverStripe\ORM\HasManyList|\IteratorAggregate<int,$class> $name()";
                            }
                        }
                        $manyMany = Config::inst()->get(
                            $className,
                            'many_many',
                            Config::UNINHERITED | Config::EXCLUDE_EXTRA_SOURCES
                        );
                        if ($manyMany) {
                            foreach ($manyMany as $name => $desc) {
                                if (is_array($desc)) {
                                    $comment .= "\n * @method \SilverStripe\ORM\ManyManyThroughList $name()";
                                } else {
                                    $exp = explode('\\', $desc);
                                    $class = array_pop($exp);
                                    $comment .= "\n * @method \SilverStripe\ORM\ManyManyList|\IteratorAggregate<int,$class> $name()";
                                }
                            }
                        }

                        $comment .= "\n */";
                        if ($shouldReplace) {
                            $node->setAttribute('comments', [
                                new Doc($comment),
                            ]);
                        }
                    }
                    return $node;
                }
            };

            $this->addTraversalTransform($visitor);
        }
    }
}
return Codemod::class;
