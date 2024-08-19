<?php

namespace OkamiChen\Database\Eloquent;

use Illuminate\Database\Eloquent\Model;

class Basic extends Model
{
    public const CREATED_AT = 'create_time';
    public const UPDATED_AT = 'update_time';
    public const DELETED_AT = 'delete_time';
}
