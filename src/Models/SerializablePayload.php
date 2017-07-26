<?php namespace Moosend\Models;

interface SerializablePayload
{
    /**
     * @return array
     */
    public function toArray();
}
