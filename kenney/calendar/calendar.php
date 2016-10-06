<?php

  # Calendar Version 4.0 - 20160427

  # Override a specific day for things such as a different type
  # of class (exams, practicum, project, etc) or for the eventual
  # snow day or holiday, takes the format of
  # $OVERRIDE = array(MONTH# => array(DAY# => 'new type',
  #                                   DAY# => 'new type'),
  #                   MONTH# => array(DAY# => 'new type'));
  $OVERRIDE = array(9 => array(5 => 'HOLIDAY'),
                    10 => array(10 => 'HOLIDAY'),
                    11 => array(11 => 'HOLIDAY', 24 => 'HOLIDAY', 25 => 'HOLIDAY'));

  # What is the default schedule for this class
  # use 1-7 (as Mon -> Fri), or use 'mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'
  $DOW = array('mon'=>'class',
               'wed'=>'lab',
               'fri'=>'class');

  # Set the Course Number
  $COURSE = 'SI123';

  # Set the Descriptive Name of the course
  $COURSENAME = 'Applied Something';

  # Set Defaults Titles for the pages (easily overriden by changing these in the pages)
  $PAGE_TITLE = "$COURSE (Fall 2016)";

  # Show Weekends
  $WEEKENDS = False;

  # When does this class start, and how long (months) is it?
  $MONTH_START = 8;
  $DAY_START = 22;
  $MONTH_END = 12;
  $YEAR = 2016;

  # Admin password to override hidden-ness!  This will allow you to see all files,
  # including those not linked, and those that have time delayed availability
  $ADMIN = 'mypassword';

  # The secret makes it harder for someone to set the SESSION Variables
  # To gain access without knowing the password...  This is used by the
  # authenticator
  $SECRET = 'A really long phrase that should be impossible to guess, you dont need to remember this...';

  # Default start page, set to true to automatically show the current days
  # lecture.  Otherwise the standard homepage will be shown.
  $DEFAULT_TODAYS_LECTURE = False;

  # Unlock a file for viewing by naming the file xxxx.MONTH.DAY.ext, OR
  # By placing that information into consilidated into a single file
  # -> CONTENTS LIKE:
  # homework/homework01<tab>month<tab>day
  $ACCESS_FILE = 'virtual/cal4.access';

  # Place a file into a directory virtually using a config file
  # -> CONTENTS LIKE:
  # notes.html<tab>homework/homework01.html<tab>COMPONENT<tab>day
  # -> Where COMPONENT in the list of $COMPONENTS below
  $CLASS_FILE = 'virtual/cal4.virtual';

  # What are the types of classes (each should be in their own
  # Directory. (type => directory name)
  $COMPONENTS = array('class'=>'class',
                      'lab'=>'lab',
                      'exam'=>'exam',
                      'project'=>'project',
                      'review'=>'review',
                      'practicum'=>'practicum');

  # What information do you want shown in each day box on the calendar
  # Items that aren't found for that day are ignored.
  $BOX = array(0 => 'title',
               1 => 'instructor',
               2 => 'reading',
               3 => 'notes',
               4 => 'lab-solution',
               5 => 'homework',
               6 => 'homework-solution');

  # For the items that appear in a box, what is their source

  # Which items from the box should be processed as html,
  # The difference being that with a link the users is redirected,
  # with the $HTML option the css will survive.
  # Note: This has higher precedence over $LINK
  $HTML = array('title' => 'index.html',
                'homework' => 'homework.html',
                'reading' => 'reading.html',
                'notes' => 'notes.html',
                'homework-solution' => 'answers.html',
                'lab-solution' => 'labanswers.html',
                'instructor' => 'instructor.html');

  # Which items from the box should cause html linking.
  $LINK = array('title' => 'lindex.html',
                'homework' => 'lhomework.html',
                'reading' => 'lreading.html',
                'notes' => 'lnotes.html',
                'lab-solution' => 'llabanswers.html',
                'homework-solution' => 'lanswers.html');

  # Which items should be handled as pdfs,
  $PDF = array('title' => 'slides.pdf',
               'homework' => 'homework.pdf',
               'reading' => 'reading.pdf',
               'homework-solution' => 'answers.pdf',
               'lab-solution' => 'labanswers.pdf',
               'instructor' => 'instructor.pdf');

  # Which items should be handled as slides,
  $PPT = array('title' => 'slides.pptx',
               'homework' => 'homework.pptx',
               'reading' => 'reading.pptx',
               'homework-solution' => 'answers.pptx',
               'lab-solution' => 'labanswers.pptx',
               'instructor' => 'instructor.pptx');

  # These items will be cut and pasted into that part of the box
  # so a good place to put one liners.  Note: only the first line
  # of the file will be used, the rest will be ignored (or used as
  # instructor notes), this is a carryover from the designs of
  # calendar V1 and V2.
  $SRC = array('homework' => 'homework.txt',
               'reading' => 'reading.txt',
               'notes' => 'notes.txt',
               'homework-solution' => 'answers.txt',
               'instructor' => 'instructor.txt');

  # Run the main calendar script
  require_once('calendar/calendar_main.php');
?>
