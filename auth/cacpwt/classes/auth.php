<?php
/**
 * PowerTrain CAC Plugin Core Functionality
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */

namespace auth_cacpwt;

use auth_plugin_base;

defined('MOODLE_INTERNAL') || die();

class auth extends auth_plugin_base {
	private $certRawIssuer = '';
	private $certUserDODID = '';
	private $certIssuer = '';
	private $certEmail = '';

	private $dodidCheckPattern = "/[0-9]{10}[0-9]*/";

	private $isValid = '0';
	private $isSubValid = '0';
	private $isIssValid = '0';
	private $isEmailValid = '0';
	private $isVerifyValid = '0';

	/**
	 * Function that initializes proxy cert data parser
	 * @param $subjectLine  string SSL_CLIENT_S_DN
	 * @param $issuerLine   string SSL_CLIENT_I_DN
	 * @param $emailLine    string SSL_CLIENT_SAN_Email_0
	 * @param $verifyLine   string SSL_CLIENT_VERIFY
	 */
	public function proxyCertDataParser(string $subjectLine, string $issuerLine, string $emailLine,
			string $verifyLine): void {
		if ((!empty($subjectLine)) && (!empty($issuerLine)))  {
			$this->decodeSubject($subjectLine);
			$this->decodeIssuer($issuerLine);
			$this->decodeEmail($emailLine);
			$this->decodeVerify($verifyLine);

			if (($this->isSubValid == '1') && ($this->isIssValid == '1') && ($this->isEmailValid == '1') &&
					($this->isVerifyValid == '1'))  {
				$this->isValid = '1';
			}
		} else {
			$this->isValid = '0';
		}
	}

	/**
	 * Function that returns if cert is valid
	 */
	public function isCertValid(): bool {
		return ($this->isValid == '1');
	}

	/**
	 * Function that returns cert issuer -- if valid
	 *
	 * @return string
	 */
	public function getCertIssuer(): string {
		if ($this->isValid == '1') {
			return $this->certIssuer;
		}

		return '';
	}

	/**
	 * Function that returns user DODID -- if valid
	 *
	 * @return string
	 */
	public function getCertUserDODID(): string {
		if ($this->isValid == '1') {
			return $this->certUserDODID;
		}

		return '';
	}

	/**
	 * Function that returns cert email -- if valid
	 *
	 * @return string
	 */
	public function getCertEmail(): string {
		if ($this->isValid == '1') {
			return $this->certEmail;
		}

		return '';
	}

	/**
	 * Function that returns cert raw issuer
	 *
	 * @return string
	 */
	public function getRawIssuer(): string {
		return $this->certRawIssuer;
	}

	/**
	 * Function that decodes Subject
	 *
	 * @param $line
	 * @return void
	 */
	private function decodeSubject($line) {
		if (empty($line)) {
			$this->isSubValid = '0';
			return;
		}

		$certIndexStart = strpos($line, 'CN=');
		$certIndexEnd = strpos($line, ',');

		if (($certIndexStart >= 0) && ($certIndexEnd > ($certIndexStart + 3))
			   && (strlen(substr($line, $certIndexStart+3, $certIndexEnd - $certIndexStart - 3)) > 3)) {
			// Split the name and the DODID
			// Tried to follow logic in ProxyCertDataParser, but...strrch is easier.
			$userInfo = substr($line, $certIndexStart + 3, $certIndexEnd - $certIndexStart - 3);
			$dodid = substr(strrchr($userInfo, '.'), 1);

			if (preg_match($this->dodidCheckPattern, $dodid)) {
				$this->certUserDODID = $dodid;
			} else {
				$this->isSubValid = '0';
				return;
			}

			$name = substr($userInfo, 0, strlen($userInfo) - strlen($dodid) - 1);

			if (strlen($name) > 0) {
				$nameParts = explode('.', $name);

				if ((count($nameParts) < 2 || (empty($nameParts[0])) || (empty($nameParts[1])))) {
					$this->isSubValid = '0';

					return;
				}

				$this->isSubValid = '1';
			} else {
				$this->isSubValid = '0';
			}
		}
	}

	/**
	 * Function that decodes Issuer
	 *
	 * @param $line
	 */
	private function decodeIssuer($line) {
		$this->isIssValid = '0';
		$this->certRawIssuer = $line;

		if (empty($line)) {
			return;
		}

		$certIndexStart = strpos($line, 'CN=');
		$certIndexEnd = strpos($line, ',');

		if (($certIndexStart >= 0) && ($certIndexEnd > ($certIndexStart + 3))
				&& (strlen(substr($line, $certIndexStart + 3, $certIndexEnd - $certIndexStart - 3)))) {
			$this->certIssuer = substr($line, $certIndexStart + 3, $certIndexEnd - $certIndexStart - 3);
			$this->isIssValid = '1';
		}
	}

	/**
	 * Function that decodes Email
	 *
	 * @param $line
	 */
	private function decodeEmail($line) {
		$this->isEmailValid = '0';

		if (empty($line)) {
			return;
		}

		$this->certEmail = $line;
		$this->isEmailValid = '1';
	}

	/**
	 * Function that decodes Verify
	 *
	 * @param $line
	 */
	private function decodeVerify($line) {
		$this->isVerifyValid = '0';

		if (empty($line)) {
			return;
		}

		if ($line == 'SUCCESS') {
			$this->isVerifyValid = '1';
		}
	}
}
