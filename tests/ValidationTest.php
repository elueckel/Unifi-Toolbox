<?php

declare(strict_types=1);
include_once __DIR__.'/stubs/Validator.php';
class ValidationTest extends TestCaseSymconValidation
{
	public function testValidateCoreStubs(): void
	{
		$this->validateLibrary(__DIR__.'/../');
	}
	public function testValidateDNSSDControl(): void
	{
		$this->validateModule(__DIR__.'/../UniFi Device Monitor');
		$this->validateModule(__DIR__.'/../UniFi Endpoint Blocker');
		$this->validateModule(__DIR__.'/../UniFi Endpoint Monitor');
		$this->validateModule(__DIR__.'/../UniFi Internet Controller');
		$this->validateModule(__DIR__.'/../UniFi Presence Manager');
	}
}
