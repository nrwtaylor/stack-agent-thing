<?php
/**
 * Word.php
 *
 * @package default
 */

namespace Nrwtaylor\StackAgentThing;

class Paragraph extends Agent
{
    /**
     *
     * @param Thing   $thing
     * @param unknown $agent_input (optional)
     */
    function init()
    {
        $this->resource_path = $GLOBALS['stack_path'] . 'resources/';
        $this->resource_path_paragraphs =
            $GLOBALS['stack_path'] . 'resources/paragraphs/';

        $this->keywords = [];

        $this->thing_report['help'] =
            "Organizes things into paragraphs. Blocks of words.";
    }

    function set()
    {
        $this->thing->Write(
            ["paragraph", "reading"],
            $this->reading
        );

        if (isset($this->paragraphs) and count($this->paragraphs) != 0) {
        } else {
            $this->thing->log($this->agent_prefix . 'did not find words.');
        }
    }

    function get()
    {
        $time_string = $this->thing->Read([
            "paragraph",
            "refreshed_at",
        ]);

        if ($time_string == false) {
            $time_string = $this->thing->time();
            $this->thing->Write(
                ["paragraph", "refreshed_at"],
                $time_string
            );
        }

        // If it has already been processed ...
        $this->reading = $this->thing->Read([
            "paragraph",
            "reading",
        ]);
    }

    /**
     *
     * @param unknown $test
     * @return unknown
     */
    function getParagraphs($test)
    {
        if ($test == false) {
            return false;
        }

        //Paragraph indicators.
        // /n/n. /n. /r/n. /n/r <br> <br><br> <p><br> etc

        explode($test, "/n");

        $delimiters = ["/n", "/r", "<br>", "<p>"];
        $delimiters_string = implode("|", $delimiters);

        $paragraphs = preg_split('/ (' . '\n' . '|' . '<br>' . ') /', $test);

        foreach ($paragraphs as $key => $paragraph) {
            $new_paragraphs[] = trim($paragraph);
        }
        //
        return $new_paragraphs;
    }

    // https://www.brainbell.com/tutorials/php/long-to-small-paragraph.html
    function makeParagraphs($text, $length = 200, $maxLength = 250)
    {
        //Text length
        $textLength = strlen($text);

        //initialize empty array to store split text
        $splitText = [];

        //return without breaking if text is already short
        if (!($textLength > $maxLength)) {
            $splitText[] = $text;
            return $splitText;
        }

        //Guess sentence completion
        $needle = '.';

        /*iterate over $text length
         as substr_replace deleting it*/

        while (strlen($text) > $length) {
            $end = strpos($text, $needle, $length);

            if ($end === false) {
                //Returns FALSE if the needle (in this case ".") was not found.
                $splitText[] = substr($text, 0);
                $text = '';
                break;
            }

            $end++;
            $splitText[] = substr($text, 0, $end);
            $text = substr_replace($text, '', 0, $end);
        }

        if ($text) {
            $splitText[] = substr($text, 0);
        }

        return $splitText;
    }

    /**
     *
     * @param unknown $string
     * @return unknown
     */
    function extractParagraphs($text, $allow_empty_paragraphs = true)
    {
        $delimiters = ["/n", "/r", "<br>", "<p>"];
        $delimiters_string = trim(implode("|", $delimiters));

        if ($allow_empty_paragraphs == false) {
            $paragraphs = preg_split('~\R~', $text, -1, PREG_SPLIT_NO_EMPTY);
        } else {
            // Allow empty paragraphs.
            $paragraphs = preg_split('~\R~', $text);
        }

        $this->paragraphs = $paragraphs;
        return $this->paragraphs;
    }

    /**
     *
     * @return unknown
     */
    function getParagraph()
    {
        if (!isset($this->paragraphs)) {
            $this->extractParagraphs($this->subject);
        }
        if (count($this->paragraphs) == 0) {
            $this->paragraph = false;
            return false;
        }
        $this->paragraph = $this->paragraphs[0];
        return $this->graph;
    }

    function randomParagraph()
    {
    }

    /**
     *
     * @param unknown $input
     * @return unknown
     */
    function nearestParagraph($input)
    {
    }

    /**
     *
     * @return unknown
     */
    public function respond()
    {
        $this->cost = 100;

        // Thing stuff
        $this->thing->flagGreen();

        // Make SMS
        $this->makeSMS();
        $this->thing_report['sms'] = $this->sms_message;

        // Make message
        $this->thing_report['message'] = $this->sms_message;

        // Make email
        $this->makeEmail();

        $this->thing_report['email'] = $this->sms_message;

        if ($this->agent_input == null) {
            $message_thing = new Message($this->thing, $this->thing_report);
            $this->thing_report['info'] = $message_thing->thing_report['info'];
        }
        $this->reading = null;
        if (isset($this->words)) {
            $this->reading = count($this->words);
        }
        $this->thing->Write(
            ["paragraph", "reading"],
            $this->reading
        );
        return $this->thing_report;
    }

    /**
     *
     */
    function makeSMS()
    {
        if (isset($this->paragraphs)) {
            if (count($this->paragraphs) == 0) {
                if (isset($this->nearest_word)) {
                    $this->sms_message =
                        "PARAGRAPH | closest match " . $this->nearest_paragraph;
                } else {
                    $this->sms_message = "PARAGRAPH | no paragraphs found";
                }

                //            $this->sms_message = "WORD | no words found";
                return;
            }

            if ($this->paragraphs[0] == false) {
                if (isset($this->nearest_paragraph)) {
                    $this->sms_message =
                        "PARAGRAPH | closest match " . $this->nearest_paragraph;
                } else {
                    $this->sms_message = "PARAGRAPH | no paragraphs found";
                }
                return;
            }

            if (count($this->paragraphs) > 1) {
                $this->sms_message = "PARAGRAPHS ARE ";
            } elseif (count($this->paragraphs) == 1) {
                $this->sms_message = "PARAGRAPH IS ";
            }
            $this->sms_message .= implode(" ", $this->paragraphs);
            return;
        }

        $this->sms_message = "PARAGRAPH | no match found";
    }

    /**
     *
     */
    function makeEmail()
    {
        $this->email_message = "PARAGRAPH | ";
    }

    /**
     *
     * @return unknown
     */
    public function readSubject()
    {
        //        $this->translated_input = $this->wordsEmoji($this->subject);
/*
        if ($this->agent_input == null) {
            $input = strtolower($this->subject);
        } else {
            $input = strtolower($this->agent_input);

            if ($input == "paragraph") {
                return;
            }
        }
*/
        $input = $this->agent_input;
        if ($this->agent_input == "" or $this->agent_input == null) {
            $input = $this->subject;
        }

        if ($this->agent_input == "paragraph") {
            return;
            //$input = $this->subject;
        }



        // test
        $this->resource_path_paragraphs =
            $GLOBALS['stack_path'] . 'resources/paragraphs/';

        $keywords = ['paragraph', 'random'];
        $pieces = explode(" ", strtolower($input));

        $this->extractParagraphs($input);
        foreach ($pieces as $key => $piece) {
            foreach ($keywords as $command) {
                if (strpos(strtolower($piece), $command) !== false) {
                    switch ($piece) {
                        case 'random':
                            $number_agent = new Number($this->thing, "number");
                            $number_agent->extractNumber($input);

                            $this->randomWord($number_agent->number);

                            if ($this->word != null) {
                                return;
                            }

                        case 'paragraph':
                            if (
                                isset($this->paragraph) and
                                $this->paragraph != null
                            ) {
                                return;
                            }

                        default:
                    }
                }
            }
        }

        if (isset($this->search_words)) {
            $this->nearest_paragraph = $this->nearestParagraph(
                $this->search_words
            );
            $status = true;

            return $status;
        }
    }

    /**
     *
     * @return unknown
     */
    function contextParagraph()
    {
        $this->paragraph_context = '
';

        return $this->paragraph_context;
    }
}
