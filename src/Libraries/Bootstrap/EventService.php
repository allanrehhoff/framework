<?php

namespace Bootstrap;

class EventService {
	/**
	 * Define your default event listeners in this function.
	 * These listeners will be added upon every request
	 * 
	 * Example event listener class defined elsewhere:
	 * 
	 * ```php
	 * <?php
	 * namespace EventListners;
	 *
	 * class UserRegistration {
	 *		public function handle(User $iUser) {
	 *			// Assuming you have the following classes loaded
	 *			\EmailService::sendWelcomeEmail($iUser);
	 *
	 *			\Logger::debug("User registered: " . $iUser->getUsername());
	 *		}
	 * }
	 * ```
	 * Use the fully qualified class name
	 * \Core\Event::addListener("controller.execute.before", \UserRegistration::class);
	 * 
	 * Closures may also be passes
	 * \Core\Event::addListener("controller.execute.before", fn(\User $iUser) => \EmailService::sendWelcomeEmail($iUser));
	 *
	 * @return void
	 */
	public function registerDefaultListeners(): void {
		// Force HTTPS redirect
		\Core\Event::addListener("core.global.init", \EventListeners\HttpsRedirect::class);
	}
}
