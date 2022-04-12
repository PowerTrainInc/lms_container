<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * PowerTrain CAC Plugin Core Functionality
 *
 * @package   auth_cacpwt
 * @copyright 2021 PowerTrain Inc {@link https://powertrain.com}
 * @license   All Rights Reserved
 */

namespace auth_cacpwt;

use auth_plugin_base;

/**
 * CAC Authentication Class.
 */
class auth extends auth_plugin_base {
    /**
     * @var string $certrawissuer Certificate raw issuer name.
     */
    private $certrawissuer = '';

    /**
     * @var string $certuserdodid Certificate DOD ID number.
     */
    private $certuserdodid = '';

    /**
     * @var string $certissuer Certificate issuer.
     */
    private $certissuer = '';

    /**
     * @var string $certemail Certificate email address.
     */
    private $certemail = '';

    /**
     * @var string $dodidcheckpattern
     */
    private $dodidcheckpattern = "/[0-9]{10}[0-9]*/";

    /**
     * @var string $isvalid Tracker for validation.
     */
    private $isvalid = '0';

    /**
     * @var string $issubvalid Tracker for sub validation.
     */
    private $issubvalid = '0';

    /**
     * @var string $isissvalid Tracker for issuer validation.
     */
    private $isissvalid = '0';

    /**
     * @var string $isemailvalid Tracker for email validation.
     */
    private $isemailvalid = '0';

    /**
     * @var string $isverifyvalid Tracker for validation of verification.
     */
    private $isverifyvalid = '0';

    /**
     * @var string $username The username extrapolated from CAC information.
     */
    public $cacpwtusername = null;

    /**
     * Function that initializes proxy cert data parser
     * @param string $subjectline SSL_CLIENT_S_DN
     * @param string $issuerline SSL_CLIENT_I_DN
     * @param string $emailline SSL_CLIENT_SAN_Email_0
     * @param string $verifyline SSL_CLIENT_VERIFY
     * @return void
     */
    public function proxy_cert_data_parser(string $subjectline, string $issuerline, string $emailline,
                                           string $verifyline): void {
        if ((!empty($subjectline)) && (!empty($issuerline))) {
            $this->decode_subject($subjectline);
            $this->decode_issuer($issuerline);
            $this->decode_email($emailline);
            $this->decode_verify($verifyline);

            if (($this->issubvalid == '1') && ($this->isissvalid == '1') && ($this->isemailvalid == '1') &&
                    ($this->isverifyvalid == '1')) {
                $this->isvalid = '1';
            }
        } else {
            $this->isvalid = '0';
        }
    }

    /**
     * Function that returns if cert is valid
     */
    public function is_cert_valid(): bool {
        return ($this->isvalid == '1');
    }

    /**
     * Function that returns cert issuer -- if valid
     *
     * @return string
     */
    public function get_cert_issuer(): string {
        if ($this->isvalid == '1') {
            return $this->certissuer;
        }

        return '';
    }

    /**
     * Function that returns user DODID -- if valid
     *
     * @return string
     */
    public function get_cert_user_dodid(): string {
        if ($this->isvalid == '1') {
            return $this->certuserdodid;
        }

        return '';
    }

    /**
     * Function that returns cert email -- if valid
     *
     * @return string
     */
    public function get_cert_email(): string {
        if ($this->isvalid == '1') {
            return $this->certemail;
        }

        return '';
    }

    /**
     * Function that returns cert raw issuer
     *
     * @return string
     */
    public function get_raw_issuer(): string {
        return $this->certrawissuer;
    }

    /**
     * Function that decodes Subject
     *
     * @param string $line
     * @return void
     */
    private function decode_subject(string $line): void {
        if (empty($line)) {
            $this->issubvalid = '0';
            return;
        }

        $certindexstart = strpos($line, 'CN=');
        $certindexend = strpos($line, ',');

        if (($certindexstart >= 0) && ($certindexend > ($certindexstart + 3))
               && (strlen(substr($line, $certindexstart + 3, $certindexend - $certindexstart - 3)) > 3)) {
            // Split the name and the DODID
            // Tried to follow logic in proxy_cert_data_parser, but...strrch is easier.
            $userinfo = substr($line, $certindexstart + 3, $certindexend - $certindexstart - 3);
            $dodid = substr(strrchr($userinfo, '.'), 1);

            if (preg_match($this->dodidcheckpattern, $dodid)) {
                $this->certuserdodid = $dodid;
            } else {
                $this->issubvalid = '0';
                return;
            }

            $name = substr($userinfo, 0, strlen($userinfo) - strlen($dodid) - 1);

            if (strlen($name) > 0) {
                $nameparts = explode('.', $name);

                if ((count($nameparts) < 2 || (empty($nameparts[0])) || (empty($nameparts[1])))) {
                    $this->issubvalid = '0';

                    return;
                }

                // Establish username based on first initial of first name and full last name.
                $this->cacpwtusername = preg_replace('/[^a-z]/', '', strtolower(substr($nameparts[1], 0, 1) . $nameparts[0]));

                $this->issubvalid = '1';
            } else {
                $this->issubvalid = '0';
            }
        }
    }

    /**
     * Function that decodes Issuer
     *
     * @param string $line
     * @return void
     */
    private function decode_issuer(string $line): void {
        $this->isissvalid = '0';
        $this->certrawissuer = $line;

        if (empty($line)) {
            return;
        }

        $certindexstart = strpos($line, 'CN=');
        $certindexend = strpos($line, ',');

        if (($certindexstart >= 0) && ($certindexend > ($certindexstart + 3))
                && (strlen(substr($line, $certindexstart + 3, $certindexend - $certindexstart - 3)))) {
            $this->certissuer = substr($line, $certindexstart + 3, $certindexend - $certindexstart - 3);
            $this->isissvalid = '1';
        }
    }

    /**
     * Function that decodes Email
     *
     * @param string $line
     * @return void
     */
    private function decode_email(string $line): void {
        $this->isemailvalid = '0';

        if (empty($line)) {
            return;
        }

        $this->certemail = $line;
        $this->isemailvalid = '1';
    }

    /**
     * Function that decodes Verify
     *
     * @param string $line
     * @return void
     */
    private function decode_verify(string $line): void {
        $this->isverifyvalid = '0';

        if (empty($line)) {
            return;
        }

        if ($line == 'SUCCESS') {
            $this->isverifyvalid = '1';
        }
    }
}
