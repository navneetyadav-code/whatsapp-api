<?php

declare(strict_types=1);

$result = ContactService::list((int) $user['id'], '', null, 50, 0);
$contacts = $result['contacts'];
$optedInCount = $result['opted_in_count'];
