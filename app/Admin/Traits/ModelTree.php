<?php

namespace App\Admin\Traits;

use Illuminate\Support\Facades\DB;

trait ModelTree
{
    /**
     * @var boolean
     */
    protected $disableOrdering = true;

    /**
     * Get all elements.
     *
     * @return mixed
     */
    public function allNodes()
    {
        $orderColumn = DB::getQueryGrammar()->wrap($this->disableOrdering ? $this->titleColumn : $this->orderColumn);
        $byOrder = $orderColumn.' = 0,'.$orderColumn;

        $self = new static();

        if ($this->queryCallback instanceof \Closure) {
            $self = call_user_func($this->queryCallback, $self);
        }

        return $self->orderByRaw($byOrder)->get()->toArray();
    }

    /**
     * Save tree order from a tree like array.
     *
     * @param array $tree
     * @param int   $parentId
     */
    public static function saveOrder($tree = [], $parentId = 0)
    {
        if (empty(static::$branchOrder)) {
            static::setBranchOrder($tree);
        }

        foreach ($tree as $branch) {
            $node = static::find($branch['id']);

            $node->{$node->getParentColumn()} = $parentId;

            if (!$node->disableOrdering) {
                $node->{$node->getOrderColumn()} = static::$branchOrder[$branch['id']];
            }

            $node->save();

            if (isset($branch['children'])) {
                static::saveOrder($branch['children'], $branch['id']);
            }
        }
    }
}