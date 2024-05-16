<?php

namespace Core\Exception;

/**
 * FileNotFound is thrown when core is missing a required file.
 * Could be a config .jsonc or a controller.
 * You should rarely have the need for catching this.
 */
class FileNotFound extends \Exception {
}
