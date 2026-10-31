<?php

$currentPage = $page ?? ($_GET['page'] ?? 'dashboard');
$userForSidebar = $user ?? current_user();

$chatUnreadCount = 0;
$chatbotRulesCount = 0;
$knowledgeCount = 0;
$toolsCount = 0;
$credentialsCount = 0;
$memoryCount = 0;
$agentMode = 'support';

$userId = (int) ($userForSidebar['id'] ?? 0);

if (!isset($initials)) {
    $nameParts = preg_split('/\s+/', trim((string) ($userForSidebar['name'] ?? 'Admin'))) ?: [];
    $initials = strtoupper(substr($nameParts[0] ?? 'A', 0, 1) . substr($nameParts[1] ?? 'D', 0, 1));
}

/*
|--------------------------------------------------------------------------
| Load sidebar counters
|--------------------------------------------------------------------------
*/

if ($userId > 0) {
    try {
        $pdo = Database::pdo();

        $stmt = $pdo->prepare(
            'SELECT agent_mode
             FROM ai_agent_settings
             WHERE user_id = ?
             LIMIT 1'
        );
        $stmt->execute([$userId]);

        $agentMode = (string) ($stmt->fetchColumn() ?: 'support');

        $stmt = $pdo->prepare(
            'SELECT COALESCE(SUM(unread_count), 0)
             FROM chat_conversations
             WHERE user_id = ?'
        );
        $stmt->execute([$userId]);

        $chatUnreadCount = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM chatbot_rules
             WHERE user_id = ?'
        );
        $stmt->execute([$userId]);

        $chatbotRulesCount = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM ai_knowledge_chunks
             WHERE user_id = ?'
        );
        $stmt->execute([$userId]);

        $knowledgeCount = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT COUNT(*)
             FROM ai_custom_tools
             WHERE user_id = ?
             AND is_active = 1'
        );
        $stmt->execute([$userId]);

        $toolsCount = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare(
            'SELECT
                (
                    SELECT COUNT(*)
                    FROM ai_provider_keys
                    WHERE user_id = ?
                    AND is_active = 1
                )
                +
                (
                    SELECT COUNT(*)
                    FROM user_smtp_credentials
                    WHERE user_id = ?
                    AND is_active = 1
                )'
        );

        $stmt->execute([$userId, $userId]);

        $credentialsCount = (int) $stmt->fetchColumn();

        $stmt = $pdo->prepare('SELECT COUNT(*) FROM ai_user_memory WHERE user_id = ?');
        $stmt->execute([$userId]);
        $memoryCount = (int) $stmt->fetchColumn();

    } catch (\Throwable $e) {
        error_log(
            'Unable to load sidebar counters: ' . $e->getMessage()
        );
    }
}

$isPersonalMode = ($agentMode === 'personal');

$aiTotalBadges = $toolsCount + $chatbotRulesCount + $credentialsCount + $memoryCount;

/*
|--------------------------------------------------------------------------
| Page matching
|--------------------------------------------------------------------------
*/

$isActive = static function (array $pages) use ($currentPage): bool {
    return in_array($currentPage, $pages, true);
};

/*
|--------------------------------------------------------------------------
| Navigation configuration
|--------------------------------------------------------------------------
|
| Smart Accordion: Active category group is open by default; others are
| collapsed to reduce cognitive load and avoid sidebar vertical scroll.
|
*/

$navigation = [

    /*
    |--------------------------------------------------------------------------
    | WORKSPACE
    |--------------------------------------------------------------------------
    */

    'workspace' => [
        'title' => 'Workspace',
        'type' => 'direct',
        'items' => [
            [
                'label' => 'Overview',
                'page' => 'dashboard',
                'pages' => ['dashboard'],
                'icon' => 'dashboard',
            ],
            [
                'label' => 'Connect WhatsApp',
                'page' => 'device',
                'pages' => ['device'],
                'icon' => 'phone',
            ],
            [
                'label' => 'Contacts',
                'page' => 'contacts',
                'pages' => ['contacts'],
                'icon' => 'users',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CUSTOMER ENGAGEMENT
    |--------------------------------------------------------------------------
    */

    'engagement' => [
        'title' => 'Customer Engagement',
        'type' => 'group',
        'icon' => 'messages',
        'items' => [
            [
                'label' => 'Live Chat',
                'page' => 'live-chat',
                'pages' => ['live-chat'],
                'icon' => 'chat',
                'mode' => 'support',
                'badge' => 'live-chat',
            ],
            [
                'label' => 'New Message',
                'page' => 'message',
                'pages' => ['message'],
                'icon' => 'edit',
            ],
            [
                'label' => 'Campaigns',
                'page' => 'campaigns',
                'pages' => ['campaigns'],
                'icon' => 'megaphone',
            ],
            [
                'label' => 'Message Queue',
                'page' => 'queue',
                'pages' => ['queue'],
                'icon' => 'queue',
            ],
            [
                'label' => 'Customer Feedback',
                'page' => 'feedback',
                'pages' => ['feedback'],
                'icon' => 'star',
            ],
            [
                'label' => 'Escalations',
                'page' => 'escalations',
                'pages' => ['escalations'],
                'icon' => 'alert',
                'mode' => 'support',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AI & AUTOMATION
    |--------------------------------------------------------------------------
    */

    'ai' => [
        'title' => 'AI & Automation',
        'type' => 'group',
        'icon' => 'ai',
        'items' => [
            [
                'label' => 'AI Agent Studio',
                'page' => 'chatbot',
                'pages' => ['chatbot', 'ai-agent'],
                'icon' => 'bot',
            ],
            [
                'label' => 'Memory & Training',
                'page' => 'memory',
                'pages' => ['memory'],
                'icon' => 'memory',
                'badge' => 'memory',
            ],
            [
                'label' => 'Knowledge Base',
                'page' => 'knowledge',
                'pages' => ['knowledge'],
                'icon' => 'book',
                'mode' => 'support',
                'badge' => 'knowledge',
            ],
            [
                'label' => 'Automation Rules',
                'page' => 'rules',
                'pages' => ['rules', 'keyword-rules'],
                'icon' => 'workflow',
                'badge' => 'rules',
            ],
            [
                'label' => 'Custom Tools',
                'page' => 'tools',
                'pages' => ['tools'],
                'icon' => 'tools',
                'badge' => 'tools',
            ],
            [
                'label' => 'App Store & Presets',
                'page' => 'app-store',
                'pages' => ['app-store', 'presets'],
                'icon' => 'store',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | CHANNELS & INTEGRATIONS
    |--------------------------------------------------------------------------
    */

    'integrations' => [
        'title' => 'Channels & Integrations',
        'type' => 'group',
        'icon' => 'integrations',
        'items' => [
            [
                'label' => 'API Keys',
                'page' => 'api-keys',
                'pages' => ['api-keys'],
                'icon' => 'key',
            ],
            [
                'label' => 'Webhooks',
                'page' => 'webhooks',
                'pages' => ['webhooks'],
                'icon' => 'webhook',
            ],
            [
                'label' => 'AI Providers & SMTP',
                'page' => 'ai-credentials',
                'pages' => ['ai-credentials', 'ai-keys'],
                'icon' => 'plug',
                'badge' => 'credentials',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | ADMINISTRATION
    |--------------------------------------------------------------------------
    */

    'administration' => [
        'title' => 'Administration',
        'type' => 'group',
        'icon' => 'shield',
        'items' => [
            [
                'label' => 'Security',
                'page' => 'security',
                'pages' => ['security'],
                'icon' => 'shield',
            ],
            [
                'label' => 'Audit Logs',
                'page' => 'audit',
                'pages' => ['audit'],
                'icon' => 'audit',
            ],
        ],
    ],
];

/*
|--------------------------------------------------------------------------
| Filter navigation by agent mode
|--------------------------------------------------------------------------
*/

foreach ($navigation as $key => &$category) {

    $category['items'] = array_values(
        array_filter(
            $category['items'],
            static function (array $item) use ($isPersonalMode): bool {
                if (
                    ($item['mode'] ?? null) === 'support'
                    && $isPersonalMode
                ) {
                    return false;
                }

                return true;
            }
        )
    );

    $category['active'] = false;

    foreach ($category['items'] as $item) {
        if ($isActive($item['pages'])) {
            $category['active'] = true;
            break;
        }
    }
}

unset($category);

/*
|--------------------------------------------------------------------------
| Badge rendering
|--------------------------------------------------------------------------
*/

$getBadge = static function (string $type) use (
    $chatUnreadCount,
    $knowledgeCount,
    $toolsCount,
    $chatbotRulesCount,
    $credentialsCount,
    $memoryCount
): ?array {

    switch ($type) {

        case 'live-chat':
            return [
                'value' => $chatUnreadCount > 0
                    ? $chatUnreadCount
                    : 'Live',
                'class' => $chatUnreadCount > 0
                    ? 'sidebar-badge-live'
                    : 'sidebar-badge-muted',
            ];

        case 'knowledge':
            return $knowledgeCount > 0
                ? [
                    'value' => $knowledgeCount,
                    'class' => 'sidebar-badge-muted',
                ]
                : null;

        case 'tools':
            return $toolsCount > 0
                ? [
                    'value' => $toolsCount,
                    'class' => 'sidebar-badge-indigo',
                ]
                : null;

        case 'rules':
            return $chatbotRulesCount > 0
                ? [
                    'value' => $chatbotRulesCount,
                    'class' => 'sidebar-badge-amber',
                ]
                : null;

        case 'credentials':
            return $credentialsCount > 0
                ? [
                    'value' => $credentialsCount,
                    'class' => 'sidebar-badge-emerald',
                ]
                : null;

        case 'memory':
            return $memoryCount > 0
                ? [
                    'value' => $memoryCount,
                    'class' => 'sidebar-badge-fuchsia',
                ]
                : null;
    }

    return null;
};

/*
|--------------------------------------------------------------------------
| SVG icon library
|--------------------------------------------------------------------------
*/

$iconPaths = [

    'dashboard' =>
        '<rect x="3" y="3" width="7" height="7" rx="1.5"/>
         <rect x="14" y="3" width="7" height="5" rx="1.5"/>
         <rect x="14" y="12" width="7" height="9" rx="1.5"/>
         <rect x="3" y="13" width="7" height="8" rx="1.5"/>',

    'users' =>
        '<path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
         <circle cx="10" cy="7" r="4"/>
         <path d="M20 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>',

    'messages' =>
        '<path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8z"/>',

    'chat' =>
        '<path d="M21 15a4 4 0 0 1-4 4H8l-5 3V7a4 4 0 0 1 4-4h10a4 4 0 0 1 4 4z"/>
         <path d="M8 10h.01M12 10h.01M16 10h.01"/>',

    'edit' =>
        '<path d="M12 20h9"/>
         <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L8 18l-4 1 1-4z"/>',

    'megaphone' =>
        '<path d="m3 11 18-5v12L3 13z"/>
         <path d="M11.6 16.8 13 21l4-1-2.1-4.9"/>
         <path d="M3 11v2"/>',

    'queue' =>
        '<path d="M8 6h13M8 12h13M8 18h13"/>
         <path d="M3 6h.01M3 12h.01M3 18h.01"/>',

    'star' =>
        '<path d="m12 3 2.7 5.5 6.1.9-4.4 4.3 1 6.1-5.4-2.9-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z"/>',

    'alert' =>
        '<path d="M10.3 3.9 2.6 17.2A2 2 0 0 0 4.3 20h15.4a2 2 0 0 0 1.7-2.8L13.7 3.9a2 2 0 0 0-3.4 0z"/>
         <path d="M12 9v4m0 4h.01"/>',

    'ai' =>
        '<rect x="3" y="7" width="18" height="13" rx="2"/>
         <path d="M8 7V4h8v3M12 11v5m-2.5-2.5h5"/>
         <path d="M7 20v2m10-2v2"/>',

    'bot' =>
        '<rect x="4" y="7" width="16" height="13" rx="3"/>
         <path d="M12 3v4m-4 6h.01M16 13h.01M9 17h6"/>
         <path d="M2 12h2m16 0h2"/>',

    'book' =>
        '<path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
         <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>',

    'workflow' =>
        '<rect x="3" y="3" width="7" height="7" rx="1"/>
         <rect x="14" y="14" width="7" height="7" rx="1"/>
         <path d="M6.5 10v4a2 2 0 0 0 2 2H14m0-3 3.5 3-3.5 3"/>',

    'tools' =>
        '<path d="M14.7 6.3a4 4 0 0 0-5.6 5.6l-6.5 6.5a2 2 0 0 0 2.8 2.8l6.5-6.5a4 4 0 0 0 5.6-5.6l-3 3-3-3z"/>',

    'store' =>
        '<path d="M3 9h18l-1.5-6h-15z"/>
         <path d="M5 9v12h14V9M9 21v-7h6v7"/>
         <path d="M3 9a3 3 0 0 0 6 0 3 3 0 0 0 6 0 3 3 0 0 0 6 0"/>',

    'integrations' =>
        '<path d="M10 13a5 5 0 0 0 7.1 0l3-3a5 5 0 0 0-7.1-7.1l-1.7 1.7"/>
         <path d="M14 11a5 5 0 0 0-7.1 0l-3 3a5 5 0 0 0 7.1 7.1l1.7-1.7"/>',

    'phone' =>
        '<rect x="6" y="2" width="12" height="20" rx="2"/>
         <path d="M11 18h2"/>',

    'key' =>
        '<circle cx="8" cy="15" r="5"/>
         <path d="m21 2-9.6 9.6M15.5 7.5l3 3L21 8l-3-3"/>',

    'webhook' =>
        '<path d="M18 8a3 3 0 1 0-3-3"/>
         <path d="M6 16a3 3 0 1 0 0 6"/>
         <path d="M18 16a3 3 0 1 1 0 6"/>
         <path d="m8.5 14 4-7m3 7-4-7M9 19h6"/>',

    'plug' =>
        '<path d="M12 22v-5"/>
         <path d="M9 8V2m6 6V2"/>
         <path d="M5 8h14v4a7 7 0 0 1-14 0z"/>',

    'shield' =>
        '<path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11z"/>
         <path d="m9 12 2 2 4-4"/>',

    'memory' =>
        '<path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 1 1 7.072 0l-.548.547A3.374 3.374 0 0 0 14 18.469V19a2 2 0 1 1-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>',

    'audit' =>
        '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
         <path d="M14 2v6h6M8 13h8m-8 4h8"/>',

    'chevron' =>
        '<path d="m9 18 6-6-6-6"/>',

    'logout' =>
        '<path d="M10 17l5-5-5-5"/>
         <path d="M15 12H3"/>
         <path d="M12 3h6a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-6"/>',
];

/*
|--------------------------------------------------------------------------
| Icon renderer
|--------------------------------------------------------------------------
*/

$renderIcon = static function (
    string $name,
    string $class = 'sidebar-icon'
) use ($iconPaths): string {

    $path = $iconPaths[$name] ?? $iconPaths['dashboard'];

    return '<svg class="' . e($class) . '"
        fill="none"
        stroke="currentColor"
        stroke-width="1.7"
        stroke-linecap="round"
        stroke-linejoin="round"
        viewBox="0 0 24 24"
        aria-hidden="true">'
        . $path .
        '</svg>';
};

/*
|--------------------------------------------------------------------------
| Navigation URL renderer
|--------------------------------------------------------------------------
*/

$pageUrl = static function (string $pageName): string {
    return e(base_url('index.php?page=' . urlencode($pageName)));
};

/*
|--------------------------------------------------------------------------
| Navigation item renderer
|--------------------------------------------------------------------------
*/

$renderItem = static function (
    array $item,
    string $type = 'normal'
) use ($isActive, $getBadge, $renderIcon, $pageUrl, $chatUnreadCount): string {

    $active = $isActive($item['pages']);
    $badge = isset($item['badge'])
        ? $getBadge($item['badge'])
        : null;

    $class = $type === 'sub'
        ? 'sidebar-sublink'
        : ($type === 'flyout'
            ? 'sidebar-flyout-link'
            : 'sidebar-link');

    if ($active) {
        $class .= ' is-active';
    }

    $iconClass = $type === 'sub'
        ? 'sidebar-subicon'
        : 'sidebar-icon';

    $tooltipAttr = ($type === 'normal') ? ' data-tooltip="' . e($item['label']) . '"' : '';

    $html = '<a href="' . $pageUrl($item['page']) . '"
        class="' . $class . '"
        ' . ($active ? 'aria-current="page"' : '') . '
        ' . $tooltipAttr . '
        title="' . e($item['label']) . '">';

    if ($type === 'flyout') {

        $html .= '<span class="truncate">'
            . e($item['label'])
            . '</span>';

    } else {

        $html .= '<span class="sidebar-item-content">';

        $html .= '<span class="sidebar-icon-wrap relative flex items-center justify-center shrink-0">';

        $html .= $renderIcon($item['icon'], $iconClass);

        if (
            ($item['badge'] ?? '') === 'live-chat'
            && $badge !== null
            && $chatUnreadCount > 0
        ) {
            $html .= '<span class="sidebar-dot bg-emerald-500"></span>';
        }

        $html .= '</span>';

        $html .= '<span class="sidebar-label truncate">'
            . e($item['label'])
            . '</span>';

        $html .= '</span>';
    }

    if ($badge !== null) {
        $badgeIdAttr = ($type !== 'flyout' && ($item['badge'] ?? '') === 'live-chat') ? ' id="sidebarLiveChatBadge"' : '';
        $html .= '<span' . $badgeIdAttr . ' class="sidebar-badge '
            . e($badge['class'])
            . '">';

        $html .= e((string) $badge['value']);

        $html .= '</span>';
    }

    $html .= '</a>';

    return $html;
};

?>

<aside
    id="sidebar"
    class="bg-[#F8FAFC] border-r border-slate-200/80 text-slate-600
    w-[280px] lg:w-[288px] flex-shrink-0 flex flex-col z-30
    absolute inset-y-0 left-0 -translate-x-full
    md:relative md:translate-x-0 select-none"
    aria-label="Application sidebar"
>

    <!-- =========================================================
         SIDEBAR HEADER
    ========================================================== -->

    <div class="sidebar-header h-[68px] flex items-center justify-between
        px-4 sm:px-5 shrink-0 border-b border-slate-200/70
        relative transition-all">

        <a
            href="<?= $pageUrl('dashboard') ?>"
            class="sidebar-brand flex items-center gap-3 text-slate-900
            group min-w-0"
            title="WhatsGateway Dashboard"
        >

            <span class="relative flex h-9 w-9 items-center justify-center
                rounded-xl bg-white border border-slate-200 shadow-sm shrink-0">

                <img
                    src="<?= e(base_url('assets/logo_white.png')) ?>"
                    alt="WhatsGateway"
                    class="h-7 w-7 rounded-lg object-contain"
                >

            </span>

            <span class="sidebar-brand-text font-extrabold
                tracking-[-0.03em] text-[16px] truncate">
                WhatsGateway
            </span>

        </a>

        <div class="flex items-center gap-1 shrink-0">

            <button
                id="collapseSidebar"
                type="button"
                class="hidden md:flex items-center justify-center
                w-7 h-7 rounded-lg text-slate-400
                hover:text-slate-800 hover:bg-slate-200/60
                transition-colors cursor-pointer"
                aria-label="Toggle sidebar collapse"
                title="Collapse sidebar (Ctrl+B)"
            >
                <svg
                    class="sidebar-chevron w-4 h-4 transition-transform duration-200"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M11 19l-7-7 7-7m8 14l-7-7 7-7"
                    />
                </svg>
            </button>

            <button
                id="closeSidebar"
                type="button"
                class="md:hidden text-slate-400 hover:text-slate-900
                p-2 -mr-1 rounded-lg hover:bg-slate-100
                transition-colors cursor-pointer"
                aria-label="Close navigation"
            >
                <svg
                    class="w-5 h-5"
                    fill="none"
                    stroke="currentColor"
                    viewBox="0 0 24 24"
                >
                    <path
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M6 18L18 6M6 6l12 12"
                    />
                </svg>
            </button>

        </div>
    </div>


    <!-- =========================================================
         NAVIGATION (ALL DROPDOWNS OPEN BY DEFAULT)
    ========================================================== -->

    <nav
        class="sidebar-scroll flex-1 overflow-y-auto py-5 px-3
        font-medium text-[13px] space-y-5"
        aria-label="Primary navigation"
    >

        <?php foreach ($navigation as $categoryKey => $category): ?>

            <?php
                $items = $category['items'];
                $categoryActive = $category['active'];
            ?>

            <?php if (empty($items)) continue; ?>

            <section
                class="sidebar-category"
                data-category="<?= e($categoryKey) ?>"
            >

                <!-- Category Heading -->

                <div class="sidebar-category-title">
                    <?= e($category['title']) ?>
                </div>


                <?php if ($category['type'] === 'direct'): ?>

                    <!-- Direct Navigation Items -->

                    <div class="space-y-1">

                        <?php foreach ($items as $item): ?>

                            <?= $renderItem($item, 'normal') ?>

                        <?php endforeach; ?>

                    </div>


                <?php else: ?>

                    <!-- Dropdown Navigation Group: Smart Active-State Open -->

                    <?php
                        // Active category group stays open by default; others remain collapsed to prevent cognitive & scroll fatigue
                        $isGroupOpen = $categoryActive;
                    ?>
                    <div
                        class="sidebar-group <?= $isGroupOpen ? 'is-open' : '' ?>"
                        data-group="<?= e($categoryKey) ?>"
                    >

                        <button
                            type="button"
                            class="sidebar-group-btn sidebar-link
                            justify-between w-full cursor-pointer
                            select-none <?= $categoryActive ? 'group-active' : '' ?>"
                            aria-expanded="<?= $isGroupOpen ? 'true' : 'false' ?>"
                            title="<?= e($category['title']) ?>"
                        >

                            <span class="sidebar-item-content">

                                <span class="sidebar-icon-wrap relative
                                    flex items-center justify-center shrink-0">

                                    <?= $renderIcon($category['icon']) ?>

                                    <?php if (
                                        $categoryKey === 'ai'
                                        && $aiTotalBadges > 0
                                    ): ?>
                                        <span class="sidebar-dot bg-indigo-500"></span>
                                    <?php elseif (
                                        $categoryKey === 'engagement'
                                        && $chatUnreadCount > 0
                                    ): ?>
                                        <span class="sidebar-dot bg-emerald-500"></span>
                                    <?php endif; ?>

                                </span>

                                <span class="sidebar-label truncate">
                                    <?= e($category['title']) ?>
                                </span>

                            </span>


                            <span class="sidebar-group-meta flex items-center
                                gap-1.5 shrink-0">

                                <?php if (
                                    $categoryKey === 'ai'
                                    && $toolsCount > 0
                                ): ?>

                                    <span class="sidebar-badge sidebar-badge-indigo">
                                        <?= $toolsCount ?>
                                    </span>

                                <?php elseif (
                                    $categoryKey === 'engagement'
                                    && $chatUnreadCount > 0
                                ): ?>

                                    <span class="sidebar-badge sidebar-badge-live">
                                        <?= $chatUnreadCount ?>
                                    </span>

                                <?php endif; ?>

                                <svg
                                    class="sidebar-chevron-down w-3.5 h-3.5
                                    text-slate-400 transition-transform duration-200"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-width="2"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M19 9l-7 7-7-7"
                                    />
                                </svg>

                            </span>

                        </button>


                        <!-- Child Navigation: Visible by default with smooth indentation -->

                        <div
                            class="sidebar-group-content pl-4 pr-1
                            space-y-0.5 mt-1 border-l-2
                            border-slate-200/80 ml-5 transition-all"
                        >

                            <?php foreach ($items as $item): ?>

                                <?= $renderItem($item, 'sub') ?>

                            <?php endforeach; ?>

                        </div>


                        <!-- Collapsed Sidebar Flyout -->

                        <div class="sidebar-flyout-menu hidden absolute
                            left-[74px] top-0 w-56 bg-white
                            rounded-xl border border-slate-200/90
                            shadow-xl py-2 px-1 z-50">

                            <div class="px-3 py-2 text-[11px]
                                font-bold text-slate-400 uppercase
                                tracking-wider border-b
                                border-slate-100 mb-1">

                                <?= e($category['title']) ?>

                            </div>

                            <?php foreach ($items as $item): ?>

                                <?= $renderItem($item, 'flyout') ?>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php endif; ?>

            </section>

        <?php endforeach; ?>

    </nav>


    <!-- =========================================================
         USER PROFILE FOOTER
    ========================================================== -->

    <div class="sidebar-footer p-3 border-t border-slate-200/70
        bg-[#F8FAFC] shrink-0">

        <div class="sidebar-user-card flex items-center gap-3
            px-3 py-2.5 rounded-xl bg-white
            border border-slate-200/80 shadow-subtle transition-all"
            data-tooltip="<?= e($userForSidebar['name'] ?? 'Admin') ?>">

            <div
                class="w-8 h-8 rounded-lg bg-slate-900 text-white
                flex items-center justify-center text-[10px]
                font-bold font-mono shrink-0"
                title="<?= e($userForSidebar['name'] ?? 'Admin') ?>"
            >
                <?= e($initials) ?>
            </div>


            <div class="sidebar-user-meta flex-1 truncate min-w-0">

                <p class="text-[12px] font-semibold text-slate-900
                    truncate leading-tight">

                    <?= e($userForSidebar['name'] ?? 'Admin') ?>

                </p>

                <p class="text-[10px] text-slate-400 truncate
                    font-mono mt-0.5">

                    <?= $isPersonalMode
                        ? 'Personal workspace'
                        : 'Workspace owner' ?>

                </p>

            </div>


            <form
                method="post"
                action="<?= e(base_url('index.php?page=logout')) ?>"
                title="Sign out"
                class="sidebar-user-logout shrink-0"
            >

                <input
                    type="hidden"
                    name="csrf_token"
                    value="<?= e(csrf_token()) ?>"
                >

                <button
                    type="submit"
                    class="text-slate-400 hover:text-rose-600
                    p-1.5 rounded-lg hover:bg-rose-50
                    transition-colors cursor-pointer"
                    aria-label="Sign out"
                >
                    <?= $renderIcon('logout', 'w-4 h-4') ?>
                </button>

            </form>

        </div>

    </div>

    <script>
        (function() {
            try {
                document.querySelectorAll('#sidebar .sidebar-group').forEach(function(group) {
                    var groupKey = group.getAttribute('data-group');
                    if (!groupKey) return;
                    var stored = localStorage.getItem('sidebar_group_' + groupKey);
                    var btn = group.querySelector('.sidebar-group-btn');
                    if (stored === 'open') {
                        group.classList.add('is-open');
                        if (btn) btn.setAttribute('aria-expanded', 'true');
                    } else if (stored === 'closed') {
                        group.classList.remove('is-open');
                        if (btn) btn.setAttribute('aria-expanded', 'false');
                    }
                });
            } catch (e) {}
        })();
    </script>

</aside>
