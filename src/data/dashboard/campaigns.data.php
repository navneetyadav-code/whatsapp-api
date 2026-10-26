<?php
declare(strict_types=1);

$campaigns = recent_campaigns((int) $user['id']);
$optedInCount = count(opted_in_contacts((int) $user['id']));

$editingCampaignId = max(0, (int) ($_GET['edit'] ?? 0));
$editingCampaign = $editingCampaignId ? get_campaign((int) $user['id'], $editingCampaignId) : null;
