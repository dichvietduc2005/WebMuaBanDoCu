<?php
/**
 * API Entry Point - Delegates to ApiRouter
 * 
 * Backward compatible with existing endpoints:
 *   - /api.php?action=search_suggestions
 *   - /api.php?module=search&action=suggestions
 *   - /api.php?module=notification&action=get
 */

require_once __DIR__ . '/../Api/ApiRouter.php';

// Create router and handle request
$router = new ApiRouter();
$router->handleRequest();