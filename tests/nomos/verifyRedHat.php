<?php
/*
 Copyright (C) 2010 Hewlett-Packard Development Company, L.P.

 This program is free software; you can redistribute it and/or
 modify it under the terms of the GNU General Public License
 version 2 as published by the Free Software Foundation.

 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License along
 with this program; if not, write to the Free Software Foundation, Inc.,
 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */

/**
 * Verify/Establish baseline of licenses found by nomos
 *
 * @param needs to have the RedHat.tar uploaded.
 *
 * @return pass or fail
 *
 * @version "$Id: $"
 *
 * Created on Oct. 6, 2010
 */

require_once ('../fossologyTestCase.php');
//require_once ('../TestEnvironment.php');
require_once('../testClasses/parseMiniMenu.php');
require_once('../testClasses/dom-parseLicenseTable.php');
require_once('../testClasses/parseFolderPath.php');
require_once('../commonTestFuncs.php');

/* Globals for test use, most tests need $URL, only login needs the others */
global $URL;

class rhelTest extends fossologyTestCase
{
	public $mybrowser;          // must have
	protected $host;

	/*
	 * Every Test needs to login so we use the setUp method for that.
	 * setUp is called before any other method by default.
	 *
	 */
	function setUp()
	{
		global $URL;
		$this->Login();
		$this->host = getHost($URL);
	}

	function testRHEL()
	{
		global $URL;

		$licBaseLine = array(
    'No License Found' => 4734,
    '(C)Apache' => 909,
    'Apache_v2.0' => 858,
    'ATT' => 812,
    'Misc-Copyright' => 787,
    '(C)U-Washington' => 385,
    'GPL_v2+' => 289,
    'CMU' => 247,
    'FSF' => 176,
    'BSD-style' => 154,
    'LGPL' => 123,
    'GPL' => 95,
    '(C)RedHat' => 82,
    'GPL_v3+' => 76,
    'Apache-possibility' => 62,
    'Debian-SPI-style' => 34,
    'GNU-Manpages' => 34,
    'LGPL_v2.1+' => 29,
    'MPL_v1.1' => 29,
    'IETF' => 28,
    'See-doc(OTHER)' => 28,
    'UnclassifiedLicense' => 28,
    'GPL-exception' => 24,
    'BSD' => 20,
    'Apache' => 17,
    'GPL_v2' => 12,
    'Possible-copyright' => 12,
    'Indemnity' => 11,
    'LikelyNot' => 10,
    'Public-domain-claim' => 9,
    'Authorship-inference' => 8,
    '(C)Stanford' => 8,
    'GPL-possibility' => 8,
    'Non-commercial!' => 7,
    'RSA-Security' => 7,
    'ATT-possibility' => 6,
    'LGPL_v2+' => 6,
    'OSL_v1.0' => 6,
    'CPL_v1.0' => 5,
    'GFDL_v1.1+' => 5,
    'GPL_v3' => 5,
    'Intel' => 5,
    'LGPL_v3+' => 4,
    'No-warranty' => 4,
    'Public-domain-ref' => 4,
    'APSL_v1.1' => 3,
    'IOS' => 3,
    'MIT-style' => 3,
    'Apache_v1.1' => 2,
    '(C)FSF' => 2,
    '(C)GPL' => 2,
    'CMU-possibility' => 2,
    '(C)Sun' => 2,
    'GFDL_v1.1' => 2,
    'GFDL_v1.2+' => 2,
    'MIT' => 2,
    'MPL' => 2,
    'Open-Publication_v1.0' => 2,
    'Zope-PL_v2.0' => 2,
    'AGFA(RESTRICTED)' => 1,
    'APSL' => 1,
    'APSL_v1.2' => 1,
    'ATT-Source_v1.2d' => 1,
    'BSD-possibility' => 1,
    'CCA' => 1,
    '(C)Intel' => 1,
    '(C)MIT' => 1,
    '(C)RedHat-Cygnus' => 1,
    'Dyade' => 1,
    'GPL_v2.1+' => 1,
    'HP-possibility' => 1,
    'ISC' => 1,
    'LGPL-possibility' => 1,
    'MacroMedia-RPSL' => 1,
    'Microsoft-possibility' => 1,
    'NOT-public-domain' => 1,
    'NPL_v1.1' => 1,
    'Patent-ref' => 1,
    'RedHat-EULA' => 1,
    'RedHat(Non-commercial)' => 1,
    'Sun' => 1,
    'Sun-BCLA' => 1,
    'Sun-possibility' => 1,
    'Sun(RESTRICTED)' => 1,
    'TeX-exception' => 1,
    'U-Wash(Free-Fork)' => 1,
    'X11' => 1,
    'zlib/libpng' => 1,
		);
		
		$licenseSummary = array(
      'Unique licenses'        => 88,
      'Licenses found'         => 5528,
      'Files with no licenses' => 4734,
      'Files'                  => 12532
		);


		print "starting testRHEL\n";

		$name = 'RedHat.tar';
		$page = $this->mybrowser->clickLink('Browse');
		$this->assertTrue($this->myassertText($page, "/>View</"),
       "verifyRedHat FAILED! >View< not found\n");
		$this->assertTrue($this->myassertText($page, "/>Info</"),
       "verifyRedHat FAILED! >Info< not found\n");
		$this->assertTrue($this->myassertText($page, "/>Download</"),
       "verifyRedHat FAILED! >Download< not found\n");
    //$page = $this->mybrowser->clickLink('Testing');
		$this->assertTrue($this->myassertText($page, "/$name/"),
       "verifyRedHat FAILED! did not find $name\n");

		/* Select archive */
		//print "CKZDB: page before call parseBMenu:\n$page\n";


		$page = $this->mybrowser->clickLink('RedHat.tar');
		$this->assertTrue($this->myassertText($page, "/1 item/"),
       "verifyRedHat FAILED! 1 item not found\n");
		$page = $this->mybrowser->clickLink('RedHat/');
		$this->assertTrue($this->myassertText($page, "/65 items/"),
       "verifyRedHat FAILED! '65 items' not found\n");
		$mini = new parseMiniMenu($page);
		$miniMenu = $mini->parseMiniMenu();
		//print "miniMenu is:\n";print_r($miniMenu) . "\n";
		$url = makeUrl($this->host, $miniMenu['Nomos License']);
		if($url === NULL) { $this->fail("verifyRedHat Failed, host/url is not set"); }
		
		$page = $this->mybrowser->get($url);
    //print "page after get of $url is:\n$page\n";
    $this->assertTrue($this->myassertText($page, '/Nomos License Browser/'),
          "verifyRedHat FAILED! Nomos License Browser Title not found\n");

	    // check that license summarys are correct
    $licSummary = new domParseLicenseTbl($page, 'licsummary', 0);
    $licSummary->parseLicenseTbl();

    foreach ($licSummary->hList as $summary)
    {
    	//print "summary is:\n";print_r($summary) . "\n";
      $key = $summary['textOrLink'];
      $this->assertEqual($licenseSummary[$key], $summary['count'],
      "verifyRedHat FAILED! $key does not equal $licenseSummary[$key],
      got $summary[count]\n");
      //print "summary is:\n";print_r($summary) . "\n";
    }

    // get the license names and 'Show' links
    $licHistogram = new domParseLicenseTbl($page, 'lichistogram',1);
    $licHistogram->parseLicenseTbl();

    if($licHistogram->noRows === TRUE)
    {
      $this->fail("FATAL! no table rows to process, there should be many for"
      . " this test, Stopping the test");
      return;
    }
    
    // verify every row against the standard by comparing the counts.
    /*
     * @todo check the show links, but to do that, need to gather another
     * standard array to match agains?  or just use the count from the
     * baseline?
     */
    foreach($licHistogram->hList as $licFound)
    {
      $key = $licFound['textOrLink'];
      $this->assertEqual($licBaseLine[$key], $licFound['count'],
      "verifyRedHat FAILED! the baseline count {$licBaseLine[$key]} does" .
      "not equal $licFound[$key],\n" .
      "Expected: $licBaseLine[$key],\n" .
      "Got: $LicFound[count]\n");
    }
	}
}
?>
