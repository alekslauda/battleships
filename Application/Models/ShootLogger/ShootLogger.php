<?php

namespace Application\Models\ShootLogger;

interface ShootLogger {

    public function logShootMessage($message, $type);

}