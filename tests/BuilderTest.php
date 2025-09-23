<?php

use PHPUnit\Framework\TestCase;
use ContentSecurityPolicy\Builder;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Builder::class)]
class BuilderTest extends TestCase {
    private Builder $builder;

    protected function setUp(): void {
        $this->builder = new Builder();
    }

    public function testStartsWithNoNonce(): void {
        $this->assertNull($this->builder->getNonce());
    }

    public function testCanGenerateNonce(): void {
        $nonce = $this->builder->generateNonce();
        
        $this->assertNotNull($nonce);
        $this->assertSame($nonce, $this->builder->getNonce());
        
        // Verify it's a valid base64 string
        $this->assertTrue(base64_decode($nonce, true) !== false);
    }

    public function testCanAddSinglePolicyValue(): void {
        $this->builder->addPolicy('script-src', "'self'");
        
        $this->assertEquals(
            "script-src 'self'",
            $this->builder->toString()
        );
    }

    public function testCanAddMultiplePolicyValues(): void {
        $this->builder->addPolicy('img-src', ["'self'", "data:"]);
        
        $this->assertEquals(
            "img-src 'self' data:",
            $this->builder->toString()
        );
    }

    public function testCanAddMultiplePolicies(): void {
        $this->builder->addPolicy('default-src', "'self'");
        $this->builder->addPolicy('img-src', ["'self'", "data:"]);
        
        $this->assertEquals(
            "default-src 'self'; img-src 'self' data:",
            $this->builder->toString()
        );
    }

    public function testRemoveDuplicateValues(): void {
        $this->builder->addPolicy('script-src', "'self'");
        $this->builder->addPolicy('script-src', "'self'");
        
        $this->assertEquals(
            "script-src 'self'",
            $this->builder->toString()
        );
    }

    public function testCanMergeArrayValues(): void {
        $this->builder->addPolicy('script-src', ["'self'"]);
        $this->builder->addPolicy('script-src', ["'unsafe-inline'"]);
        
        $this->assertEquals(
            "script-src 'self' 'unsafe-inline'",
            $this->builder->toString()
        );
    }

    public function testCanEnableNonceForPolicy(): void {
        $this->builder->enableNonceForPolicy('script-src');
        
        $nonce = $this->builder->getNonce();
        $this->assertNotNull($nonce);
        
        $expected = "script-src 'nonce-{$nonce}'";
        $this->assertEquals($expected, $this->builder->toString());
    }

    public function testNonceIsReusedWhenEnablingMultiplePolicies(): void {
        $this->builder->enableNonceForPolicy('script-src');
        $firstNonce = $this->builder->getNonce();
        
        $this->builder->enableNonceForPolicy('style-src');
        $secondNonce = $this->builder->getNonce();
        
        $this->assertSame($firstNonce, $secondNonce);
        
        $expected = "script-src 'nonce-{$firstNonce}'; style-src 'nonce-{$secondNonce}'";
        $this->assertEquals($expected, $this->builder->toString());
    }

    public function testCanMixNonceAndRegularPolicies(): void {
        $this->builder->addPolicy('default-src', "'self'");
        $this->builder->enableNonceForPolicy('script-src');
        $this->builder->addPolicy('img-src', ["'self'", "data:"]);
        
        $nonce = $this->builder->getNonce();
        $expected = "default-src 'self'; script-src 'nonce-{$nonce}'; img-src 'self' data:";
        
        $this->assertEquals($expected, $this->builder->toString());
    }

    public function testCanAddValueToNoncedPolicy(): void {
        $this->builder->enableNonceForPolicy('script-src');
        $this->builder->addPolicy('script-src', "'self'");
        
        $nonce = $this->builder->getNonce();
        $expected = "script-src 'nonce-{$nonce}' 'self'";
        
        $this->assertEquals($expected, $this->builder->toString());
    }
}