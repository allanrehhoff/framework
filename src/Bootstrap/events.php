<?php
	/**
	 * Define your event handlers here.
	 *  Example event handler class:
	 	namespace EventListners;

		class UserRegistration implements \Core\Events\ListenerInterface {
			public function handle(User $iUser) {
				// Assuming you have the following classes loaded
				\EmailService::sendWelcomeEmail($iUser);

				\Logger::log("User registered: " . $iUser->getUsername());
			}
		}
	 *
	 * Add this line to this file:
	 * \Core\Event::triggeraddListener("controller.execute.before", UserRegistration::class);
	 * 
	 * Closures may also be passes
	 * \Core\Event::triggeraddListener("controller.execute.before", fn(\User $iUser) => \EmailService::sendWelcomeEmail($iUser));
	 * 
	 */