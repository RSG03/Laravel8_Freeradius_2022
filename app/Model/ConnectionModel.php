<?php
/**
 * Created by PhpStorm.
 * User: rcinnamon
 * Date: 11/3/17
 * Time: 1:30 PM
 */

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ConnectionModel extends Model
{
    /**
     * Override method to allow inheriting connection of parent
     *
     * Define a many-to-many relationship.
     *
     * @param  string  $related
     * @param  string  $collection
     * @param  string  $relation
     * @param  string  $foreignPivotKey
     * @param  string  $relatedPivotKey
     * @param  string  $parentKey
     * @param  string  $relatedKey
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany|BelongsToMany
     */
    public function belongsToMany($related, $collection = null, $foreignPivotKey = null, $relatedPivotKey = null, $parentKey = null, $relatedKey = null, $relation = null)
    {
        // If no relationship name was passed, we will pull backtraces to get the
        // name of the calling function. We will use that function name as the
        // title of this relation since that is a great convention to apply.
        if (is_null($relation)) {
            $relation = $this->getBelongsToManyCaller();
        }

        // First, we'll need to determine the foreign key and "other key" for the
        // relationship. Once we have determined the keys we'll make the query
        // instances as well as the relationship instances we need for this.
        $foreignPivotKey = $foreignPivotKey ?: $this->getForeignKey();

        $instance = new $related;

        // get connection from parent
        $instance->setConnection(parent::getConnectionName());

        $relatedPivotKey = $relatedPivotKey ?: $instance->getForeignKey();

        // If no table name was provided, we can guess it by concatenating the two
        // models using underscores in alphabetical order. The two model names
        // are transformed to snake case from their default CamelCase also.
        if (is_null($collection)) {
            $collection = $this->joiningTable($related);
        }

        // Now we're ready to create a new query builder for the related model and
        // the relationship instances for the relation. The relations will set
        // appropriate query constraint and entirely manages the hydrations.
        $query = $instance->newQuery();

        return new BelongsToMany($query, $this, $collection, $foreignPivotKey, $relatedPivotKey, $parentKey, $relatedKey, $relation);
    }
}