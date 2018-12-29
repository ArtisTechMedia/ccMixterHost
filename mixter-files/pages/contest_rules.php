<style >
.pt0 { margin-bottom: 0px; }
.ff0 { color: #0FF; }
.mb19{ margin-bottom:0.19in; }
u { text-decoration: none; }
</style>
<H1 >
  THE <?= $A['rules']['contest_full_name']?> OFFICIAL RULES
</H1>
<P  style="MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  All submitted entries must be licensed to the general public under a <?= $A['rules']['contest_license']?> license. 
  <?= $A['rules']['contest_cosponsor_agreement']?>
</P>
<P >
  These Official Rules ("Rules") govern your
  participation in the <?= $A['rules']['contest_full_name']?> ("Contest"). Participation in
  the Contest constitutes your full and unconditional agreement to and
  acceptance of these Rules; so, it is important that you read and understand
  them.
</P>
<OL >
<LI >
<P  style="MARGIN-TOP:0.19in; MARGIN-BOTTOM:0pt">
      Sponsors: The Contest is sponsored and run by Creative Commons
      Corporation, a charitable corporation, headquartered at 543 Howard Street,
      5th Floor, San Francisco, California 94105 USA, ph. 1-415-946-3069 and
      <?= $A['rules']['co_sponsor']?> ("Co-sponsor"), <?= $A['rules']['co_sponsor_address']?>
    </P>
</LI>
<LI >
<P  class="pt0">
        Entry Period: The Contest's audio elements will go online at 
        <span ><?= CC_datefmt($A['rules']['contest_open'],'h:i a M d, Y');?></span> <?= $A['rules']['contest_timezone']?>.

        You may enter the Contest by uploading your remixes
        to
        <FONT  class="ff0"><U ><A  href="<?= $A['root-url']?>"><?= $A['root-url']?></A></U></FONT>
<span ><?= CC_datefmt($A['rules']['contest_entries_accept'],'h:i a M d, Y');?></span> <?= $A['rules']['contest_timezone']?> 
        ('Submission Start Time')
        until 
        <span ><?= CC_datefmt($A['rules']['contest_deadline'],'h:i a M d, Y');?></span> <?= $A['rules']['contest_timezone']?>
        ('Submission End Time').
      </P>
</LI>
<LI >
<P  class="pt0">
          Who is Eligible? The Contest is offered only to natural persons older
          than 15 years of age as of the date of entry. Employees, independent
          contractors, officers, and directors of Sponsors, their respective
          shareholders, agents, representatives, affiliates, subsidiaries,
          advertising, promotion and fulfillment agencies, and legal advisors
          ("Sponsors' parties"), and the immediate family members and persons
          living in the same household of such persons, are not eligible to
          participate in the Contest. Void where prohibited by law.
        </P>
</LI>
<LI >
<P  class="mb19">
            How to Enter:  To
            enter, you must submit a remix using the following material provided for the 
            contest: <?= $A['rules']['tracks_to_remix']?> ("Remix Sources"). 
            Remixes may include the audio elements which have been made available at the 
            Contest page at <?= $A['root-url']?><?= $A['rules']['contest_short_name']?>, as well as music you 
            create yourself, and/or material in the public domain, and/or other material 
            you have express permission to use and license in accordance with Rule 5.
          </P>
</LI>
</OL>
<P  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  For the avoidance of doubt, you acknowledge and agree that the musical tracks
  available at <?= $A['root-url']?><?= $A['rules']['contest_short_name']?> are licensed for use under the
  <?= $A['rules']['contest_license']?> license (and that all rights
  that are not expressly granted under this license are reserved by the Co-sponsor.
  You further acknowledge and agree that you must attribute <?= $A['rules']['attribution']?> whenever you use Remix Sources
  (ie. "Remix using elements from <?= $A['rules']['co_sponsor']?>"), but not in such a
  way as to imply any endorsement, approval or affiliation with or by <?= $A['rules']['attribution']?>.
</P>
<P  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  IF YOU USE CONTENT THAT YOU ARE NOT AUTHORIZED TO USE, YOU ARE NOT ENTITLED TO
  ENTER THIS COMPETITION AND YOUR ENTRY WILL AUTOMATICALLY BE DISQUALIFIED BY
  THE SPONSORS AND NOT CONSIDERED BY THE JUDGES.
</P>
<P  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  Submit your track(s) online after Submission Start Time and before
  Submission Stop Time with a completed <?= $A['rules']['contest_name']?> Entry Form for each track you submit.
</P>
<P  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  Any number of persons may be entered with regard to an individual recording.
  However, each individual entrant must be listed on the Contest Entry Form in
  order to be eligible for a prize.
</P>
<P  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  Any person or group may enter as many times as desired, but each individual or
  group entrant is only eligible for one prize.
</P>
<P  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  To be eligible for the prizes (described below in (7)), an entrant or group of
  named entrants, must be the sole author and copyright owner of the remix(es),
  or must have express permission (via license or otherwise) to use or
  incorporate those portions of the remix(es) authored or owned by third parties
  and license the remix(es) in accordance with these Rules. Any materials that
  infringe the rights of any third party (ie. materials used without express
  permission of the copyright owner) may not be used.
</P>
<OL  start="5">
<LI >
<P  style="MARGIN-TOP:0.19in; MARGIN-BOTTOM:0pt">
      Format of Entries: Entries must be in the form of an audio recording and
      in MP3 format. Duration of an entry may not exceed 5 minutes. All eligible
      entries must be received by Sponsors via
      <FONT  class="ff0"><U ><A  href="<?= $A['root-url']?>"><?= $A['root-url']?></A></U></FONT>
      by Submission Stop Time.
      
      Entries that finish uploading after Submission Stop Time are not eligible. Sponsors are 
      not responsible for late, lost, delayed, damaged, misdirected, incomplete, illegible, or
      unintelligible entries. Incomplete, illegible, or unintelligible entries are not eligible.
    </P>
</LI>
<LI >
<P  class="pt0">
        What others may do with your remix(es): By submitting a track as part of
        the Contest to
        <FONT  class="ff0"><U ><A  href="<?= $A['root-url']?>"><?= $A['root-url']?></A></U></FONT>,
        you agree to license that track to the rest of the world under a
        <?= $A['rules']['conest_license']?> license available at
        <FONT  class="ff0"><U ><A  href="<?= $A['rules']['conest_license_url']?>"><?= $A['rules']['conest_license_url']?></A></U></FONT>.
        All eligible entries will be made available to the general public for
        download on the
        <FONT  class="ff0"><U ><A  href="<?= $A['root-url']?>"><?= $A['root-url']?></A></U></FONT>
        website under the <?= $A['rule']['contest_license']?>
        license. Sponsors reserve the right to evaluate each entry's eligibility
        under the Rules as well as for compliance with the US Copyright Act and
        any and all other applicable laws. Entries may be removed from the
        <FONT  class="ff0"><U ><A  href="<?= $A['root-url']?>"><?= $A['root-url']?></A></U></FONT>
        site and disqualified from the Contest at the discretion of the
        Sponsors. By submitting an entry, you are representing and warranting
        that the content in your entry is authorized to be remixed by you,
        uploaded to the
        <FONT  class="ff0"><U ><A  href="<?= $A['root-url']?>"><?= $A['root-url']?></A></U></FONT>
        site and licensed to the general public under a <?= $A['rule']['contest_license']?> license. 
        By submitting your track, you
        also agree and acknowledge that you will receive no royalties from
        Sponsors, other contestants, or members of the general public who use
        your track consistent with the <?= $A['rule']['contest_license']?> license.
      </P>
</LI>
<LI >
<P  class="pt0">
          Prizes: <?= $A['rules']['conest_prize']?>
       </P>
<P >
          Sponsors
          may modify or edit winning remixes to ensure the audio quality or for
          any purpose that Sponsors deem necessary or desirable. Sponsors
          reserve the right to undertake, or to instruct their representatives
          to undertake, such reasonable editing or modifications. All prize
          expenses and/or services not specified herein are not included and are
          the sole responsibility of the winner(s). No alternative prize, cash
          equivalent, or other substitution is permitted except by Sponsors, at
          Sponsors' sole discretion, in the event of prize unavailability.
          Prizes are nontransferable. All federal, provincial, state and/or
          local taxes are the sole responsibility of the winners.
        </P>
</LI>
<LI >
<P  class="pt0">
            Judging: The winning remix will be determined by the Sponsors based
            on their criteria determined by the Sponsors and will include such
            attributes as creativity and sound production quality.
          </P>
</LI>
</OL>
<P  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  All grading decisions are final. Sponsors reserve the right to disqualify any
  entry that is, in Sponsors' discretion, inappropriate, offensive, or demeaning
  to Sponsors' reputation or goodwill, or contrary to Sponsors' mission or these
  Rules. Once the grading of the entries has been completed, the Sponsors will
  notify the winner via email and/or telephone using the contact information
  submitted by the entrants in the <?= $A['rules']['contest_name']?> Entry Form. If a
  winner fails to respond within 14 days of the date of the notification, that
  winner will be disqualified and the next highest scoring track will be
  selected as an alternate winner and notified according to this procedure.
</P>
<P >
  <?= $A['rules']['winning_proc']?>
</P>
<OL  start="9">
<LI >
<P  style="MARGIN-TOP:0.19in; MARGIN-BOTTOM:0pt">
      General Terms and Conditions: By participating, entrants agree that the
      Sponsors' parties are not responsible or liable for, and are released and
      held harmless from: (i) telephone, electronic, hardware or software
      program, network, Internet, or computer malfunctions, failures, or
      difficulties of any kind; (ii) any condition caused by events beyond the
      control of Sponsors that may cause the Contest to be disrupted or
      corrupted; (iii) any printing or typographical errors in any materials
      associated with the Contest; (iv) any and all losses, damages, rights,
      claims and actions of any kind in connection with or resulting from
      participation in the Contest or acceptance of any prize, including without
      limitation, personal injury, death, and property damage, and claims based
      on publicity rights, defamation, or invasion of privacy. Sponsors reserve
      the right, in their sole discretion, to suspend or cancel Contest at any
      time if a computer virus, bug, or other technical problem corrupts the
      administration, security, or proper conduct of the Contest. All issues and
      questions concerning the construction, validity, interpretation, and
      enforceability of these Rules, or the rights and obligations of
      participant and Sponsors in connection with the Contest, shall be governed
      by, and construed in accordance with the laws of the State of California,
      without giving effect to any choice of law or conflict of law rules or
      provisions (whether of the State of California or any other jurisdiction),
      that would cause the application of the laws of any jurisdiction other
      than the State of California.
    </P>
</LI>
<LI >
<P  class="pt0">
        Winners List/Official Rules: A copy of these Official Rules and a
        winners list may be obtained by sending an email request to
        info@creativecommons.org, or by fax request (1-415-946-3001). Requests
        for winners list must be received by <?= $A['rules']['conest_list_req_deadline']?>.
      </P>
</LI>
<LI >
<P  class="pt0">
          Privacy: By entering into this Contest you consent to the use of your
          name as set out in the ccMixter Privacy Policy available at:
          <FONT  class="ff0"><U ><A  href="<?= $A['root-url']?>privacy"><?= $A['root-url']?>privacy</A></U></FONT>
          regarding use of all material entered and/or submitted for this
          Contest or otherwise collected by the ccMixter site.
        </P>
</LI>
<LI >
<P  class="mb19">
            DMCA Compliance: Sponsors comply with the provisions of the Digital
            Millennium Copyright Act (DMCA). In compliance with the DMCA, a
            Designated Agent has been established with proper documentation sent
            to the US Copyright Office. If you have a concern regarding the use
            of copyrighted material on the ccMixter site, which is hosted by
            Creative Commons, please contact the agent designated to respond to
            reports alleging copyright infringement. The designated agent for
            Creative Commons to receive notification of claimed infringement
            under Title II of the DMCA is:
          </P>
</LI>
</OL>
<P  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
  Mia Garlick<br  />
  dmca@creativecommons.org<br  />
  543 Howard Street, Fifth Floor<br  />
  San Francisco, CA 94105<br  />
  Tel: 1-415-946-3073<br  />
  Fax: 1-415-946-3001
</P>
<p  style="MARGIN-LEFT:0.5in; MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
NOTE: The address information above is for use ONLY for DMCA compliance purposes. All other inquiries regarding the contest or ccmixter.org should be directed to the site administrators using the link at the bottom of every page on the ccmixter.org site.
</p>
<OL  start="13">
<LI >
<P  style="MARGIN-TOP:0.19in; MARGIN-BOTTOM:0.19in">
      Notices: &copy; 2007 Creative Commons Corporation. Creative Commons and the
      "Double C" logo are trademarks of Creative Commons Corporation.
      <?= $A['rules']['sponsor_copyright']?>
    </P>
</LI>
</OL>