<?php

namespace OkamiChen\Database\Traits;

trait TimeTrait
{

    public function setCreateTimeAttribute($value): void
    {
        $this->attributes[$this->getCreatedAtColumn()] = strtotime($value);
    }

    public function setUpdateTimeAttribute($value): void
    {
        $this->attributes[$this->getUpdatedAtColumn()] = strtotime($value);
    }

    public function setDeleteTimeAttribute($value): void
    {
        $this->attributes[$this->getDeletedAtColumn()] = strtotime($value);
    }
}
