<?php

// File generated from our OpenAPI spec

namespace rtbStripe\Terminal;

/**
 * A Connection Token is used by the Stripe Terminal SDK to connect to a reader.
 *
 * Related guide: <a
 * href="https://stripe.com/docs/terminal/creating-locations">Fleet Management</a>.
 *
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property string $location The id of the location that this connection token is scoped to.
 * @property string $secret Your application should pass this token to the Stripe Terminal SDK.
 */
class ConnectionToken extends \rtbStripe\ApiResource
{
    const OBJECT_NAME = 'terminal.connection_token';

    use \rtbStripe\ApiOperations\Create;
}
