<?php
declare(strict_types=1);

$campaigns = recent_campaigns((int) $user['id'], 50);
$optedInCount = count(opted_in_contacts((int) $user['id']));

$tab = (string) ($_GET['tab'] ?? 'ledger');
if (!in_array($tab, ['ledger', 'create', 'test'], true)) {
    $tab = 'ledger';
}

$editingCampaignId = max(0, (int) ($_GET['edit'] ?? 0));
$editingCampaign = $editingCampaignId ? get_campaign((int) $user['id'], $editingCampaignId) : null;
