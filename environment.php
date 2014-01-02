<?php
/**
 * Detect the environment and define some variables using putenv():
 *
 * FOL_ENVIRONMENT: The environment name (by default: development)
 * FOL_BASE_HOST: The absolute host used (by default http://localhost in cli and autodetected in http)
 * FOL_BASE_URL: The absolute url used (by default is empty)
*/

putenv('FOL_ENVIRONMENT=development');
