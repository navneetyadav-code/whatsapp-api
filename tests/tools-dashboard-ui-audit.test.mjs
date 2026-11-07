import { describe, it } from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';

describe('Tools & Integrations Dashboard UI & AJAX Audit', () => {
    const toolsPagePath = path.resolve('src/pages/dashboard/tools.php');
    const indexPath = path.resolve('public/index.php');

    const toolsContent = fs.readFileSync(toolsPagePath, 'utf8');
    const indexContent = fs.readFileSync(indexPath, 'utf8');

    it('Requirement 1: 6/6 Active badge is removed while logged calls count is retained', () => {
        // activeToolsBadge should no longer exist in the HTML
        assert.ok(!toolsContent.includes('id="activeToolsBadge"'), 'activeToolsBadge element must be removed');
        assert.ok(!toolsContent.includes('/<?= count($userTools) ?> Active'), 'X/Y Active badge text must be removed');

        // logged calls badge must be retained
        assert.ok(toolsContent.includes('logged calls'), 'Logged calls indicator must be retained');
        assert.ok(toolsContent.includes('id="invocationsBadge"'), 'invocationsBadge element must exist');
    });

    it('Requirement 2: Export ZIP and Import ZIP are combined into a single button with a dropdown', () => {
        // Dropdown container and trigger
        assert.ok(toolsContent.includes('id="zipDropdownContainer"'), 'zipDropdownContainer must exist');
        assert.ok(toolsContent.includes('id="zipDropdownTrigger"'), 'zipDropdownTrigger button must exist');
        assert.ok(toolsContent.includes('id="zipDropdownMenu"'), 'zipDropdownMenu must exist');

        // Dropdown options
        assert.ok(toolsContent.includes('Export ZIP'), 'Export ZIP option must be inside dropdown');
        assert.ok(toolsContent.includes('Import ZIP'), 'Import ZIP option must be inside dropdown');
        assert.ok(toolsContent.includes('toggleExportMode()'), 'Export action must be wired to toggleExportMode()');
        assert.ok(toolsContent.includes('openToolsImportModal()'), 'Import action must be wired to openToolsImportModal()');

        // Separate standalone Export ZIP / Import ZIP buttons in header must not exist
        assert.ok(!toolsContent.includes('id="toolsExportToggleBtnText"'), 'Old standalone export toggle text must be replaced');
    });

    it('Requirement 3: Presets button is completely removed from the header toolbar', () => {
        // Header actions toolbar check
        const headerToolbar = toolsContent.substring(0, toolsContent.indexOf('<!-- Compact Whitelisted Admin Phones Bar'));
        assert.ok(!headerToolbar.includes('<span>Presets</span>'), 'Presets button must not exist in header actions');
        assert.ok(!headerToolbar.includes('openPresetsModal()'), 'openPresetsModal() must not be triggered from header actions');
    });

    it('Requirement 4: Add Tool form is completely redesigned with modern structure and AJAX submission', () => {
        // Drawer container and form
        assert.ok(toolsContent.includes('id="customToolDrawer"'), 'customToolDrawer modal must exist');
        assert.ok(toolsContent.includes('id="customToolForm"'), 'customToolForm must exist');
        assert.ok(toolsContent.includes('handleCustomToolSubmit(event)'), 'Form must use handleCustomToolSubmit AJAX handler');

        // Categories
        assert.ok(toolsContent.includes('value="rest_api"'), 'REST API category must be supported');
        assert.ok(toolsContent.includes('value="webhook"'), 'Webhook category must be supported');
        assert.ok(toolsContent.includes('value="shared_env_api"'), 'Production category must be supported');

        // Fields
        assert.ok(toolsContent.includes('id="modalDisplayName"'), 'Tool title/display name field must exist');
        assert.ok(toolsContent.includes('id="modalToolSlug"'), 'Tool slug field must exist');
        assert.ok(toolsContent.includes('id="modalDescription"'), 'AI description field must exist');
        assert.ok(toolsContent.includes('id="modalRestMethod"'), 'REST HTTP method select must exist');
        assert.ok(toolsContent.includes('id="modalRestUrl"'), 'REST URL input must exist');
        assert.ok(toolsContent.includes('id="modalRestAuth"'), 'REST Auth header input must exist');
        assert.ok(toolsContent.includes('id="modalHeadersJson"'), 'REST Headers JSON input must exist');
        assert.ok(toolsContent.includes('id="modalRestBodyTemplate"'), 'REST Body template input must exist');
        assert.ok(toolsContent.includes('id="modalWebhookUrl"'), 'Webhook URL input must exist');
        assert.ok(toolsContent.includes('id="modalSharedLiveUrl"'), 'Production base URL input must exist');

        // Parameters builder
        assert.ok(toolsContent.includes('id="parameterCardsContainer"'), 'parameterCardsContainer must exist');
        assert.ok(toolsContent.includes('addParameterCard('), 'addParameterCard() function must exist');

        // Active switch
        assert.ok(toolsContent.includes('id="modalIsActive"'), 'modalIsActive checkbox must exist');
    });

    it('Requirement 5: Delete button enables multi-tool selection and bulk deletion', () => {
        // Toolbar delete button
        assert.ok(toolsContent.includes('id="bulkDeleteToggleBtn"'), 'bulkDeleteToggleBtn must exist in toolbar');
        assert.ok(toolsContent.includes('toggleDeleteMode()'), 'bulkDeleteToggleBtn must trigger toggleDeleteMode()');

        // Floating delete action bar
        assert.ok(toolsContent.includes('id="deleteSelectionBar"'), 'deleteSelectionBar must exist');
        assert.ok(toolsContent.includes('id="deleteSelectionCountBadge"'), 'deleteSelectionCountBadge must exist');
        assert.ok(toolsContent.includes('id="submitBulkDeleteBtn"'), 'submitBulkDeleteBtn must exist');
        assert.ok(toolsContent.includes('selectAllDeleteTools()'), 'selectAllDeleteTools() must be wired');
        assert.ok(toolsContent.includes('deselectAllDeleteTools()'), 'deselectAllDeleteTools() must be wired');
        assert.ok(toolsContent.includes('submitBulkDelete()'), 'submitBulkDelete() must be wired');

        // Individual card delete indicators
        assert.ok(toolsContent.includes('delete-indicator-wrap'), 'Cards must have delete-indicator-wrap for selection');

        // Backend bulk delete support in public/index.php
        assert.ok(indexContent.includes('page === \'custom-tool-delete\''), 'custom-tool-delete handler must exist in public/index.php');
        assert.ok(indexContent.includes('WHERE id IN ('), 'custom-tool-delete must support deleting multiple IDs via WHERE id IN');
        assert.ok(indexContent.includes('deleted_ids'), 'custom-tool-delete JSON response must include deleted_ids');
    });

    it('Requirement 6: Zero page reloads for all operations and bottom-right toast notification system', () => {
        // No window.location.reload()
        assert.ok(!toolsContent.includes('window.location.reload()'), 'window.location.reload() must not exist in tools.php');
        assert.ok(!toolsContent.includes('location.reload()'), 'location.reload() must not exist in tools.php');

        // Toast container at bottom-right
        assert.ok(toolsContent.includes('id="toastContainer"'), 'toastContainer must exist');
        assert.ok(toolsContent.includes('fixed bottom-5 right-5'), 'toastContainer must be fixed at bottom-5 right-5');
        assert.ok(toolsContent.includes('function showToast('), 'showToast function must be defined');

        // AJAX handlers for all operations
        assert.ok(toolsContent.includes('async function handleToolToggle('), 'handleToolToggle must be async without page reload');
        assert.ok(toolsContent.includes('async function handleSingleToolDelete('), 'handleSingleToolDelete must be async without page reload');
        assert.ok(toolsContent.includes('async function submitBulkDelete('), 'submitBulkDelete must be async without page reload');
        assert.ok(toolsContent.includes('async function handleAdminPhonesSubmit('), 'handleAdminPhonesSubmit must be async without page reload');
        assert.ok(toolsContent.includes('async function handleCustomToolSubmit('), 'handleCustomToolSubmit must be async without page reload');
        assert.ok(toolsContent.includes('async function saveGeneratedToolDirectly('), 'saveGeneratedToolDirectly must be async without page reload');

        // Dynamic DOM card rendering
        assert.ok(toolsContent.includes('function renderToolCardHtml('), 'renderToolCardHtml must exist for dynamic rendering');
        assert.ok(toolsContent.includes('function upsertToolCardInDom('), 'upsertToolCardInDom must exist for dynamic updates');
        assert.ok(toolsContent.includes('async function refreshToolsList('), 'refreshToolsList must fetch custom-tools-list via AJAX');

        // public/index.php custom-tools-list endpoint
        assert.ok(indexContent.includes('page === \'custom-tools-list\''), 'custom-tools-list endpoint must exist in public/index.php');
    });

    it('Requirement 7: "RBAC" tag is completely removed from the UI', () => {
        // RBAC badge near Admin Phone Numbers must be removed
        const adminSection = toolsContent.substring(
            toolsContent.indexOf('<!-- Compact Whitelisted Admin Phones Bar'),
            toolsContent.indexOf('<!-- Segmented Main View Switcher')
        );
        assert.ok(!adminSection.includes('>RBAC<'), 'RBAC badge must not exist in admin phones section');
        assert.ok(!adminSection.includes('RBAC'), 'RBAC text must not exist in admin phones section');

        // RBAC label in import preview must be removed
        assert.ok(!toolsContent.includes('<span>RBAC:'), 'RBAC prefix must not exist in import previews');
    });
});
