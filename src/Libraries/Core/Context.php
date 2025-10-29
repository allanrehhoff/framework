<?php

namespace Core;

/**
 * Holds information about the current state of the application
 * Such as the dispatched controller class and method name.
 */
final class Context {
	/**
	 * @var ClassName The controller class being dispatched/executed
	 */
	private ClassName $className;

	/**
	 * @var MethodName The method name being dispatched/executed
	 */
	private MethodName $methodName;

	/**
	 * Create a new Context
	 */
	public function __construct() {
	}

	/**
	 * Set the current route information
	 * 
	 * @param array{0: ClassName, 1: MethodName} $route A tuple containing [ClassName, MethodName]
	 * @return void
	 */
	public function setRoute(array $route): void {
		$this->className = $route[0];
		$this->methodName = $route[1];
	}

	/**
	 * Get the current route as an array
	 * 
	 * @return array An array containing the current controller class and method name
	 */
	public function getRoute(): array {
		return [$this->className, $this->methodName];
	}

	/**
	 * Get the current controller class
	 * 
	 * @return ClassName|null The current controller class or null if not set
	 */
	public function getDispatchedClassName(): null|ClassName {
		return $this->className;
	}

	/**
	 * Get the current method name
	 * 
	 * @return MethodName|null The current method name or null if not set
	 */
	public function getDispatchedMethodName(): null|MethodName {
		return $this->methodName;
	}

	/**
	 * Check if the current route matches a specific controller class
	 * 
	 * @param ClassName $iClassName The class name to check
	 * @return bool True if current controller matches
	 */
	public function isClassName(ClassName $iClassName): bool {
		return $this->className && $this->className->toString() === $iClassName->toString();
	}

	/**
	 * Check if the current route matches a specific method
	 * 
	 * @param MethodName $iMethodName The method name to check
	 * @return bool True if current method matches
	 */
	public function isMethodName(MethodName $iMethodName): bool {
		return $this->methodName && $this->methodName->toString() === $iMethodName->toString();
	}
}
