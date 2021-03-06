<?php
// This file is part of the customcertpwt module for Moodle - http://moodle.org/
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
 * Language strings for the customcertpwt module.
 *
 * @package    mod_customcertpwt
 * @copyright  2021 PowerTrain Inc
 * @license    All Rights Reserved
 */

$string['activity'] = 'Activity';
$string['addcertpage'] = 'Add page';
$string['addelement'] = 'Add element';
$string['awardedto'] = 'Awarded to';
$string['cannotverifyallcertificates'] = 'You do not have the permission to verify all certificates on the site.';
$string['certificate'] = 'Certificate';
$string['code'] = 'Code';
$string['copy'] = 'Copy';
$string['coursetimereq'] = 'Required minutes in course';
$string['coursetimereq_help'] = 'Enter here the minimum amount of time, in minutes, that a student must be logged into the course before they will be able to receive
the certificate.';
$string['createtemplate'] = 'Create template';
$string['customcertpwt:addinstance'] = 'Add a new custom certificate instance';
$string['customcertpwt:manage'] = 'Manage a custom certificate';
$string['customcertpwt:manageemailstudents'] = 'Manage email students setting';
$string['customcertpwt:manageemailteachers'] = 'Manage email teachers setting';
$string['customcertpwt:manageemailothers'] = 'Manage email others setting';
$string['customcertpwt:manageverifyany'] = 'Manage verification setting';
$string['customcertpwt:managerequiredtime'] = 'Manage time required setting';
$string['customcertpwt:manageprotection'] = 'Manage protection setting';
$string['customcertpwt:receiveissue'] = 'Receive a certificate';
$string['customcertpwt:view'] = 'View a custom certificate';
$string['customcertpwt:viewreport'] = 'View course report';
$string['customcertpwt:viewallcertificates'] = 'View all certificates';
$string['customcertpwt:verifyallcertificates'] = 'Verify all certificates on the site';
$string['customcertpwt:verifycertificate'] = 'Verify a certificate';
$string['customcertpwtsettings'] = 'Custom certificate settings';
$string['deletecertpage'] = 'Delete page';
$string['deleteconfirm'] = 'Delete confirmation';
$string['deleteelement'] = 'Delete element';
$string['deleteelementconfirm'] = 'Are you sure you want to delete this element?';
$string['deleteissueconfirm'] = 'Are you sure you want to delete this certificate issue?';
$string['deleteissuedcertificates'] = 'Delete issued certificates';
$string['deletepageconfirm'] = 'Are you sure you want to delete this certificate page?';
$string['deletetemplateconfirm'] = 'Are you sure you want to delete this certificate template?';
$string['deliveryoptiondownload'] = 'Send to the browser and force a file download';
$string['deliveryoptioninline'] = 'Send the file inline to the browser';
$string['deliveryoptions'] = 'Delivery options';
$string['description'] = 'Description';
$string['duplicate'] = 'Duplicate';
$string['duplicateconfirm'] = 'Duplicate confirmation';
$string['duplicatetemplateconfirm'] = 'Are you sure you want to duplicate this certificate template?';
$string['editcustomcertpwt'] = 'Edit certificate';
$string['editelement'] = 'Edit element';
$string['edittemplate'] = 'Edit template';
$string['elementname'] = 'Element name';
$string['elementname_help'] = 'This will be the name used to identify this element when editing a certificate. Note: this will not displayed on the PDF.';
$string['elementplugins'] = 'Element plugins';
$string['elements'] = 'Elements';
$string['elements_help'] = 'This is the list of elements that will be displayed on the certificate.

Please note: The elements are rendered in this order. The order can be changed by using the arrows next to each element.';
$string['elementwidth'] = 'Width';
$string['elementwidth_help'] = 'Specify the width of the element - \'0\' means that there is no width constraint.';
$string['emailnonstudentbody'] = 'Attached is the certificate \'{$a->certificatename}\' for \'{$a->userfullname}\' for the course \'{$a->coursefullname}\'.';
$string['emailnonstudentbodyplaintext'] = 'Attached is the certificate \'{$a->certificatename}\' for \'{$a->userfullname}\' for the course \'{$a->coursefullname}\'.';
$string['emailnonstudentcertificatelinktext'] = 'View certificate report';
$string['emailnonstudentgreeting'] = 'Hi';
$string['emailnonstudentsubject'] = '{$a->coursefullname}: {$a->certificatename}';
$string['emailstudentbody'] = 'Attached is your certificate \'{$a->certificatename}\' for the course \'{$a->coursefullname}\'.';
$string['emailstudentbodyplaintext'] = 'Attached is your certificate \'{$a->certificatename}\' for the course \'{$a->coursefullname}\'.';
$string['emailstudentcertificatelinktext'] = 'View certificate';
$string['emailstudentgreeting'] = 'Dear {$a}';
$string['emailstudentsubject'] = '{$a->coursefullname}: {$a->certificatename}';
$string['emailstudents'] = 'Email students';
$string['emailstudents_help'] = 'If set this will email the students a copy of the certificate when it becomes available. <strong>Warning:</strong> Setting this to \'Yes\' before you have finished creating the certificate will email the student an incomplete certificate.';
$string['emailteachers'] = 'Email teachers';
$string['emailteachers_help'] = 'If set this will email the teachers a copy of the certificate when it becomes available. <strong>Warning:</strong> Setting this to \'Yes\' before you have finished creating the certificate will email the teacher an incomplete certificate.';
$string['emailothers'] = 'Email others';
$string['emailothers_help'] = 'If set this will email the email addresses listed here (separated by a comma) with a copy of the certificate when it becomes available. <strong>Warning:</strong> Setting this field before you have finished creating the certificate will email the addresses an incomplete certificate.';
$string['exampledatawarning'] = 'Some of these values may just be an example to ensure positioning of the elements is possible.';
$string['font'] = 'Font';
$string['font_help'] = 'The font used when generating this element.';
$string['fontcolour'] = 'Colour';
$string['fontcolour_help'] = 'The colour of the font.';
$string['fontsize'] = 'Size';
$string['fontsize_help'] = 'The size of the font in points.';
$string['getcustomcertpwt'] = 'View certificate';
$string['gradeoutcome'] = 'Outcome';
$string['height'] = 'Height';
$string['height_help'] = 'This is the height of the certificate PDF in mm. For reference an A4 piece of paper is 297mm high and a letter is 279mm high.';
$string['invalidcode'] = 'Invalid code supplied.';
$string['invalidcolour'] = 'Invalid colour chosen, please enter a valid HTML colour name, or a six-digit, or three-digit hexadecimal colour.';
$string['invalidelementwidth'] = 'Please enter a positive number.';
$string['invalidposition'] = 'Please select a positive number for position {$a}.';
$string['invalidheight'] = 'The height has to be a valid number greater than 0.';
$string['invalidmargin'] = 'The margin has to be a valid number greater than 0.';
$string['invalidwidth'] = 'The width has to be a valid number greater than 0.';
$string['landscape'] = 'Landscape';
$string['leftmargin'] = 'Left margin';
$string['leftmargin_help'] = 'This is the left margin of the certificate PDF in mm.';
$string['listofissues'] = 'Recipients: {$a}';
$string['load'] = 'Load';
$string['loadtemplate'] = 'Load template';
$string['loadtemplatemsg'] = 'Are you sure you wish to load this template? This will remove any existing pages and elements for this certificate.';
$string['managetemplates'] = 'Manage templates';
$string['managetemplatesdesc'] = 'This link will take you to a new screen where you will be able to manage templates used by Custom certificate activities in courses.';
$string['modify'] = 'Modify';
$string['modulename'] = 'Custom Certificate PWT';
$string['modulenameplural'] = 'Custom certificates';
$string['modulename_help'] = 'This module allows for the dynamic generation of PDF certificates.';
$string['modulename_link'] = 'Custom_certificate_module';
$string['mycertificates'] = 'My certificates';
$string['mycertificatesdescription'] = 'These are the certificates you have been issued by either email or downloading manually.';
$string['name'] = 'Name';
$string['nametoolong'] = 'You have exceeded the maximum length allowed for the name';
$string['nocustomcertpwts'] = 'There are no certificates for this course';
$string['noimage'] = 'No image';
$string['norecipients'] = 'No recipients';
$string['notemplates'] = 'No templates';
$string['notissued'] = 'Not awarded';
$string['notverified'] = 'Not verified';
$string['options'] = 'Options';
$string['page'] = 'Page {$a}';
$string['pluginadministration'] = 'Custom certificate administration';
$string['pluginname'] = 'Custom certificate - PowerTrain';
$string['portrait'] = 'Portrait';
$string['posx'] = 'Position X';
$string['posx_help'] = 'This is the position in mm from the top left corner you wish the element\'s reference point to locate in the x direction.';
$string['posy'] = 'Position Y';
$string['posy_help'] = 'This is the position in mm from the top left corner you wish the element\'s reference point to locate in the y direction.';
$string['preventcopy'] = 'Prevent copy';
$string['preventcopy_desc'] = 'Enable protection from copy action.';
$string['preventprint'] = 'Prevent print';
$string['preventprint_desc'] = 'Enable protection from print action.';
$string['preventmodify'] = 'Prevent modify';
$string['preventmodify_desc'] = 'Enable protection from modify action.';
$string['print'] = 'Print';
$string['privacy:metadata:customcertpwt_issues'] = 'The list of issued certificates';
$string['privacy:metadata:customcertpwt_issues:code'] = 'The code that belongs to the certificate';
$string['privacy:metadata:customcertpwt_issues:customcertpwtid'] = 'The ID of the certificate';
$string['privacy:metadata:customcertpwt_issues:emailed'] = 'Whether or not the certificate was emailed';
$string['privacy:metadata:customcertpwt_issues:timecreated'] = 'The time the certificate was issued';
$string['privacy:metadata:customcertpwt_issues:userid'] = 'The ID of the user who was issued the certificate';
$string['rearrangeelements'] = 'Reposition elements';
$string['rearrangeelementsheading'] = 'Drag and drop elements to change where they are positioned on the certificate.';
$string['receiveddate'] = 'Awarded on';
$string['refpoint'] = 'Reference point location';
$string['refpoint_help'] = 'The reference point is the location of an element from which its x and y coordinates are determined. It is indicated by the \'+\' that appears in the centre or corners of the element.';
$string['replacetemplate'] = 'Replace';
$string['requiredtimenotmet'] = 'You must spend at least a minimum of {$a->requiredtime} minutes in the course before you can access this certificate.';
$string['rightmargin'] = 'Right margin';
$string['rightmargin_help'] = 'This is the right margin of the certificate PDF in mm.';
$string['save'] = 'Save';
$string['saveandclose'] = 'Save and close';
$string['saveandcontinue'] = 'Save and continue';
$string['savechangespreview'] = 'Save changes and preview';
$string['savetemplate'] = 'Save template';
$string['search:activity'] = 'Custom certificate - activity information';
$string['setprotection'] = 'Set protection';
$string['setprotection_help'] = 'Choose the actions you wish to prevent users from performing on this certificate.';
$string['showposxy'] = 'Show position X and Y';
$string['showposxy_desc'] = 'This will show the X and Y position when editing of an element, allowing the user to accurately specify the location.

This isn\'t required if you plan on solely using the drag and drop interface for this purpose.';
$string['taskemailcertificate'] = 'Handles emailing certificates.';
$string['templatename'] = 'Template name';
$string['templatenameexists'] = 'That template name is currently in use, please choose another.';
$string['topcenter'] = 'Center';
$string['topleft'] = 'Top left';
$string['topright'] = 'Top right';
$string['type'] = 'Type';
$string['uploadimage'] = 'Upload image';
$string['uploadimagedesc'] = 'This link will take you to a new screen where you will be able to upload images. Images uploaded using
this method will be available throughout your site to all users who are able to create a certificate.';
$string['verified'] = 'Verified';
$string['verify'] = 'Verify';
$string['verifyallcertificates'] = 'Allow verification of all certificates';
$string['verifyallcertificates_desc'] = 'When this setting is enabled any person (including users not logged in) can visit the link \'{$a}\' in order to verify any certificate on the site, rather than having to go to the verification link for each certificate.

Note - this only applies to certificates where \'Allow anyone to verify a certificate\' has been set to \'Yes\' in the certificate settings.';
$string['verifycertificate'] = 'Verify certificate';
$string['verifycertificatedesc'] = 'This link will take you to a new screen where you will be able to verify certificates on the site';
$string['verifycertificateanyone'] = 'Allow anyone to verify a certificate';
$string['verifycertificateanyone_help'] = 'This setting enables anyone with the certificate verification link (including users not logged in) to verify a certificate.';
$string['width'] = 'Width';
$string['width_help'] = 'This is the width of the certificate PDF in mm. For reference an A4 piece of paper is 210mm wide and a letter is 216mm wide.';
