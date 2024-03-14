<?php

namespace App\Http\Controllers;

use App\Helpers\Classes\Helper;
use App\Models\Company;
use App\Models\OpenAIGenerator;
use App\Models\Product;
use App\Models\Setting;
use App\Models\SettingTwo;
use App\Models\UserOpenai;
use App\Models\UserOpenaiChat;
use App\Models\Usage;
use App\Services\VectorService;
use Exception;
use GuzzleHttp\Client;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;
use OpenAI;
use OpenAI\Laravel\Facades\OpenAI as FacadesOpenAI;

class AIController extends Controller
{
    protected $client;

    protected $settings;

    protected $settings_two;

    const STABLEDIFFUSION = 'stablediffusion';

    const STORAGE_S3 = 's3';

    const STORAGE_LOCAL = 'public';

    public function __construct()
    {
        //Settings
        $this->settings = Setting::first();
        $this->settings_two = SettingTwo::first();
        if ($this->settings?->user_api_option) {
            $apiKeys = explode(',', auth()->user()?->api_keys);
        } else {
            $apiKeys = explode(',', $this->settings?->openai_api_secret);
        }
        $apiKey = $apiKeys[array_rand($apiKeys)];
        config(['openai.api_key' => $apiKey]);
        set_time_limit(120);
    }

    public function buildOutput(Request $request)
    {
        $user = Auth::user();

        if ($request->post_type != 'ai_image_generator' && $user->remaining_words <= 0 && $user->remaining_words != -1) {
            return response()->json(['errors' => 'You have no remaining words. Please upgrade your plan.'], 400);
        }

        $image_generator = $request->image_generator;
        $post_type = $request->post_type;

        //SETTINGS
        $number_of_results = $request->number_of_results;
        $maximum_length = $request->maximum_length;
        $creativity = $request->creativity;

        $language = $request->language;
        try {
            $language = explode('-', $language);
            if (count($language) > 1 && LaravelLocalization::getSupportedLocales()[$language[0]]['name']) {
                $ek = $language[1];
                $language = LaravelLocalization::getSupportedLocales()[$language[0]]['name'];
                $language .= " $ek";
            } else {
                $language = $request->language;
            }
        } catch (\Throwable $th) {
            $language = $request->language;
            Log::error($language);
        }

        $negative_prompt = $request->negative_prompt;
        $tone_of_voice = $request->tone_of_voice;

        //POST TITLE GENERATOR
        if ($post_type == 'post_title_generator') {
            $your_description = $request->your_description;
            $prompt = "Post title about $your_description in language $language .Generate $number_of_results post titles. Tone $tone_of_voice.";
        }

        //ARTICLE GENERATOR
        if ($post_type == 'article_generator') {
            $article_title = $request->article_title;
            $focus_keywords = $request->focus_keywords;
            $prompt = "Generate article about $article_title. Focus on $focus_keywords. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different articles. Tone of voice must be $tone_of_voice";
        }

        //SUMMARY GENERATOR SUMMARIZER SUMMARIZE TEXT
        if ($post_type == 'summarize_text') {
            $text_to_summary = $request->text_to_summary;
            $tone_of_voice = $request->tone_of_voice;

            $prompt = "Summarize the following text: $text_to_summary in $language using a tone of voice that is $tone_of_voice. The summary should be no longer than $maximum_length words and set the creativity to $creativity in terms of creativity. Generate $number_of_results different summaries.";
        }

        //PRODUCT DESCRIPTION
        if ($post_type == 'product_description') {
            $product_name = $request->product_name;
            $description = $request->description;

            $prompt = "Write product description for $product_name. The language is $language. Maximum length is $maximum_length. Creativity is $creativity between 0 to 1. see the following information as a starting point: $description. Generate $number_of_results different product descriptions. Tone $tone_of_voice.";
        }

        //PRODUCT NAME
        if ($post_type == 'product_name') {
            $seed_words = $request->seed_words;
            $product_description = $request->product_description;

            $prompt = "Generate product names that will appeal to customers who are interested in $seed_words. These products should be related to $product_description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different product names. Tone of voice must be $tone_of_voice";
        }

        //TESTIMONIAL REVIEW GENERATOR
        if ($post_type == 'testimonial_review') {
            $subject = $request->subject;
            $prompt = "Generate testimonial for $subject. Include details about how it helped you and what you like best about it. Be honest and specific, and feel free to get creative with your wording Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different testimonials. Tone of voice must be $tone_of_voice";
        }

        //PROBLEM AGITATE SOLUTION
        if ($post_type == 'problem_agitate_solution') {
            $description = $request->description;

            $prompt = "Write Problem-Agitate-Solution copy for the $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. problem-agitate-solution. Tone of voice must be $tone_of_voice Generate $number_of_results different Problem-Afitate-Solution.";
        }

        //BLOG SECTION
        if ($post_type == 'blog_section') {
            $description = $request->description;

            $prompt = " Write me blog section about $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different blog sections. Tone of voice must be $tone_of_voice";
        }

        //BLOG POST IDEAS
        if ($post_type == 'blog_post_ideas') {
            $description = $request->description;

            $prompt = "Write blog post article ideas about $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different blog post ideas. Tone of voice must be $tone_of_voice";
        }

        //BLOG INTROS
        if ($post_type == 'blog_intros') {
            $title = $request->title;
            $description = $request->description;

            $prompt = "Write blog post intro about title: $title. And the description is $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different blog intros. Tone of voice must be $tone_of_voice";
        }

        //BLOG CONCLUSION
        if ($post_type == 'blog_conclusion') {
            $title = $request->title;
            $description = $request->description;

            $prompt = "Write blog post conclusion about title: $title. And the description is $description.Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different blog conclusions. Tone of voice must be $tone_of_voice";
        }

        //FACEBOOK ADS
        if ($post_type == 'facebook_ads') {
            $title = $request->title;
            $description = $request->description;

            $prompt = "Write facebook ads text about title: $title. And the description is $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different facebook ads text. Tone of voice must be $tone_of_voice";
        }

        //YOUTUBE VIDEO DESCRIPTION
        if ($post_type == 'youtube_video_description') {
            $title = $request->title;

            $prompt = "write youtube video description about $title. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different youtube video descriptions. Tone of voice must be $tone_of_voice";
        }

        //YOUTUBE VIDEO TITLE
        if ($post_type == 'youtube_video_title') {
            $description = $request->description;

            $prompt = "Craft captivating, attention-grabbing video titles about $description for YouTube rankings. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different youtube video titles. Tone of voice must be $tone_of_voice";
        }

        //YOUTUBE VIDEO TAG
        if ($post_type == 'youtube_video_tag') {
            $title = $request->title;

            $prompt = "Generate tags and keywords about $title for youtube video. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different youtube video tags. Tone of voice must be $tone_of_voice";
        }

        //INSTAGRAM CAPTIONS
        if ($post_type == 'instagram_captions') {
            $title = $request->title;

            $prompt = "Write instagram post caption about $title. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different instagram captions. Tone of voice must be $tone_of_voice";
        }

        //INSTAGRAM HASHTAG
        if ($post_type == 'instagram_hashtag') {
            $keywords = $request->keywords;

            $prompt = "Write instagram hastags for $keywords. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different instagram hashtags. Tone of voice must be $tone_of_voice";
        }

        //SOCIAL MEDIA POST TWEET
        if ($post_type == 'social_media_post_tweet') {
            $title = $request->title;

            $prompt = "Write in 1st person tweet about $title. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different tweets. Tone of voice must be $tone_of_voice";
        }

        //SOCIAL MEDIA POST BUSINESS
        if ($post_type == 'social_media_post_business') {
            $company_name = $request->company_name;
            $provide = $request->provide;
            $description = $request->description;

            $prompt = "Write in company social media post, company name: $company_name. About: $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different social media posts. Tone of voice must be $tone_of_voice";
        }

        //FACEBOOK HEADLINES
        if ($post_type == 'facebook_headlines') {
            $title = $request->title;
            $description = $request->description;

            $prompt = "Write Facebook ads title about title: $title. And description is $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different facebook ads title. Tone of voice must be $tone_of_voice";
        }

        //GOOGLE ADS HEADLINES
        if ($post_type == 'google_ads_headlines') {
            $product_name = $request->product_name;
            $description = $request->description;
            $audience = $request->audience;

            $prompt = "Write Google ads headline product name: $product_name. Description is $description. Audience is $audience. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different google ads headlines. Tone of voice must be $tone_of_voice";
        }

        //GOOGLE ADS DESCRIPTION
        if ($post_type == 'google_ads_description') {
            $product_name = $request->product_name;
            $description = $request->description;
            $audience = $request->audience;

            $prompt = "Write google ads description product name: $product_name. Description is $description. Audience is $audience. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different google ads description. Tone of voice must be $tone_of_voice";
        }

        //CONTENT REWRITE
        if ($post_type == 'content_rewrite') {
            $contents = $request->contents;

            $prompt = "Rewrite content:  '$contents'. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different rewrited content. Tone of voice must be $tone_of_voice";
        }

        //PARAGRAPH GENERATOR
        if ($post_type == 'paragraph_generator') {
            $description = $request->description;
            $keywords = $request->keywords;

            $prompt = "Generate one paragraph about:  '$description'. Keywords are $keywords. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different paragraphs. Tone of voice must be $tone_of_voice";
        }

        //Pros & Cons
        if ($post_type == 'pros_cons') {
            $title = $request->title;
            $description = $request->description;

            $prompt = "Generate pros & cons about title:  '$title'. Description is $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different pros&cons. Tone of voice must be $tone_of_voice";
        }

        // META DESCRIPTION
        if ($post_type == 'meta_description') {
            $title = $request->title;
            $description = $request->description;
            $keywords = $request->keywords;

            $prompt = "Generate website meta description site name: $title. Description is $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different meta descriptions. Tone of voice must be $tone_of_voice";
        }

        // FAQ Generator (All datas)
        if ($post_type == 'faq_generator') {
            $title = $request->title;
            $description = $request->description;

            $prompt = "Answer like faq about subject: $title Description is $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different faqs. Tone of voice must be $tone_of_voice";
        }

        // Email Generator
        if ($post_type == 'email_generator') {
            $subject = $request->subject;
            $description = $request->description;

            $prompt = "Write email about title: $subject, description: $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different emails. Tone of voice must be $tone_of_voice";
        }

        // Email Answer Generator
        if ($post_type == 'email_answer_generator') {
            $description = $request->description;

            $prompt = "answer this email content: $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different email answers. Tone of voice must be $tone_of_voice";
        }

        // Newsletter Generator
        if ($post_type == 'newsletter_generator') {
            $description = $request->description;
            $subject = $request->subject;
            $title = $request->title;

            $prompt = "generate newsletter template about product_title: $title, reason: $subject description: $description. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different newsletter template. Tone of voice must be $tone_of_voice";
        }

        // Grammar Correction
        if ($post_type == 'grammar_correction') {
            $description = $request->description;

            $prompt = "Correct this to standard $language. Text is '$description'. Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different grammar correction. Tone of voice must be $tone_of_voice";
        }

        // TL;DR summarization
        if ($post_type == 'tldr_summarization') {
            $description = $request->description;

            $prompt = "$description. Tl;dr Maximum $maximum_length words. Creativity is $creativity between 0 and 1. Language is $language. Generate $number_of_results different tl;dr. Tone of voice must be $tone_of_voice";
        }

        if ($post_type == 'ai_rewriter') {
            $content_rewrite = $request->content_rewrite;
            $rewrite_mode = $request->rewrite_mode;

            $prompt = "Original Content: $content_rewrite.\n\n\nMust Rewrite content with $rewrite_mode mode differently with original content. Result language is $language \n";
        }

        if ($post_type == 'ai_image_generator') {
            $imageParam = $request->all();
            // $description = $request->description;
            // $prompt = "$description";
            // $size = $request->size;
            // $style = $request->image_style;
            // $lighting = $request->image_lighting;
            // $mood = $request->image_mood;
            // $number_of_images = (int)$request->image_number_of_images;
        }

        if ($post_type == 'ai_video') {
            $videoParam = $request->all();
        }

        if ($post_type == 'ai_code_generator') {
            $description = $request->description;
            $code_language = $request->code_language;
            $prompt = "Write a code about $description, in $code_language";
        }

		$post = OpenAIGenerator::where('slug', $post_type)->first();

        if ($post->custom_template == 1) {
            $custom_template = OpenAIGenerator::find($request->openai_id);
            $prompt = $custom_template->prompt;
            foreach (json_decode($custom_template->questions) as $question) {
                $question_name = '**'.$question->name.'**';
                $prompt = str_replace($question_name, $request[$question->name], $prompt);
            }

            $prompt .= " in $language language. Number of results should be $number_of_results. And the maximum length of $maximum_length characters";
        }

		if ($post->type == 'youtube') {
            $language = $request->language;
            $youtube_action = $request->youtube_action;
			if ($youtube_action == 'blog') {
				$prompt = "You are blog writer. Turn the given transcript text into a blog post in and translate to {$language} language. Group the content and create a subheading (witth HTML-h2) for each group. Content:";
			} elseif ($youtube_action == 'short') {
				$prompt = "You are transcript editor. Make sense of the given content and explain the main idea. Your result must be in {$language} language. Content:";
			} elseif ($youtube_action == 'list') {
				$prompt = "You are transcript editor. Make sense of the given content and make a list main ideas. Your result must be in {$language} language. Content:";
			} elseif ($youtube_action == 'tldr') {
				$prompt = "You are transcript editor. Make short TLDR. Your result must be in {$language} language. Content:";
			} elseif ($youtube_action == 'prons_cons') {
				$prompt = "You are transcript editor. Make short pros and cons. Your result must be in {$language} language. Content:";
			}

			$api_url = 'https://magicai-yt-video-post-api.vercel.app/api/transcript'; // Endpoint URL
            $response = Http::post($api_url, [
                'video_url' => $request->url,
                'language' => 'en',
            ]);
            if ($response->failed()) {
                return response()->json([
                    'message' => [$response->body()],
                ], 419);
            } else {
                $response_code = $response->status();
                $response_body = $response->json();
                if ($response_code === 200) {
                    $data = $response_body['result'];
                    foreach ($data as $transcript) {
                        $prompt .= $transcript['text'].'<br>';
                    }
					$prompt .= ". \n";
                } else {
                    return response()->json([
                        'message' => [$response_body['error']],
                    ], 419);
                }
            }
        }

		if ($post->type == 'rss') {
            $prompt = "write blog post about {$request->title}. Group the content and create a subheading (with HTML-h2) for each group.";
        }


        // check if there is a company input included in the request
        if ($request->company) {
            $company = Company::find($request->company);
            $product = Product::find($request->product);
            if ($company) {
				if(!isset($prompt)){
					$prompt = '';
				}
				$type = $product->type == 0 ? 'Service' : 'Product';
                $prompt .= ".\n Focus on my company and {$type}'s information: \n";
                // Company information
                if ($company->name) {
                    $prompt .= "The company's name is {$company->name}. ";
                }
                // explode industry
                $industry = explode(',', $company->industry);
                $count = count($industry);
                if ($count > 0) {
                    $prompt .= 'The company is in the ';
                    foreach ($industry as $index => $ind) {
                        $prompt .= $ind;
                        if ($index < $count - 1) {
                            $prompt .= ' and ';
                        }
                    }
                }

                if ($company->website) {
                    $prompt .= ". The company's website is {$company->website}. ";
                }

                if ($company->target_audience) {
                    $prompt .= "The company's target audience is: {$company->target_audience}. ";
                }

                if ($company->tagline) {
                    $prompt .= "The company's tagline is {$company->tagline}. ";
                }

                if ($company->description) {
                    $prompt .= "The company's description is {$company->description}. ";
                }
                if ($product) {
                    // Product information
                    if ($product->key_features) {
                        $prompt .= "The {$product->type}'s key features are {$product->key_features}. ";
                    }

                    if ($product->name) {
                        $prompt .= "The {$product->type}'s name is {$product->name}. \n";
                    }
                }
            }
        }

        if ($post->type == 'text' || $post->type == 'rss' || $post->type == 'youtube') {
            return $this->textOutput($prompt, $post, $creativity, $maximum_length, $number_of_results, $user);
        }

        if ($post->type == 'code') {
            return $this->codeOutput($prompt, $post, $user);
        }

        if ($post->type == 'image') {
            return $this->imageOutput($imageParam, $post, $user);
        }

        if ($post->type == 'video') {
            return $this->videoOutput($videoParam, $post, $user);
        }

        if ($post->type == 'audio') {
            $file = $request->file('file');

            return $this->audioOutput($file, $post, $user);
        }
    }

    public function streamedTextOutput(Request $request)
    {
        $settings = $this->settings;
        $settings_two = $this->settings_two;
        $message_id = $request->message_id;
        $message = UserOpenai::whereId($message_id)->first();
        $prompt = $message->input;

        $youtube_url = $request->youtube_url;
        $rss_image = $request->rss_image;

        $creativity = $request->creativity;
        $maximum_length = $request->maximum_length;
        $number_of_results = $request->number_of_results;

        return response()->stream(function () use ($prompt, $message_id, $settings, $creativity, $maximum_length, $number_of_results, $youtube_url, $rss_image) {

            try {
                if ($settings->openai_default_model == 'text-davinci-003') {
                    $stream = FacadesOpenAI::completions()->createStreamed([
                        'model' => 'text-davinci-003',
                        'prompt' => $prompt,
                        'temperature' => (int) $creativity,
                        'max_tokens' => (int) $maximum_length,
                        'n' => (int) $number_of_results,
                    ]);
                } else {
                    if ((int) $number_of_results > 1) {
                        $prompt = $prompt.' number of results should be '.(int) $number_of_results;
                    }
                    $stream = FacadesOpenAI::chat()->createStreamed([
                        'model' => $this->settings->openai_default_model,
                        'messages' => [
                            ['role' => 'user', 'content' => $prompt],
                        ],
                    ]);
                }
            } catch (\Exception $exception) {
                $messageError = 'Error from API call. Please try again. If error persists again please contact system administrator with this message '.$exception->getMessage();
                echo "data: $messageError";
                echo "\n\n";
                ob_flush();
                flush();
                echo 'data: [DONE]';
                echo "\n\n";
                ob_flush();
                flush();
                usleep(50000);
            }

            $total_used_tokens = 0;
            $output = '';
            $responsedText = '';

            // Youtube Thumbnail
            if ($youtube_url) {
                $parsedUrl = parse_url($youtube_url);
                if (isset($parsedUrl['query'])) {
                    parse_str($parsedUrl['query'], $queryParameters);
                    if (isset($queryParameters['v'])) {
                        $video_id = $queryParameters['v'];
                    }
                }
                $video_thumbnail = sprintf('https://img.youtube.com/vi/%s/maxresdefault.jpg', $video_id);

                $contents = file_get_contents($video_thumbnail);
                $nameOfImage = "youtube-$video_id.jpg";

                //save file on local storage or aws s3
                Storage::disk('public')->put($nameOfImage, $contents);
                $path = '/uploads/'.$nameOfImage;
                $uploadedFile = new File(substr($path, 1));

                if (SettingTwo::first()->ai_image_storage == 's3') {
                    try {
                        $aws_path = Storage::disk('s3')->put('', $uploadedFile);
                        unlink(substr($path, 1));
                        $path = Storage::disk('s3')->url($aws_path);
                    } catch (\Exception $e) {
                        return response()->json(['status' => 'error', 'message' => 'AWS Error - '.$e->getMessage()]);
                    }
                }

                $output = "<img src=\"$path\" style=\"width:100%\"><br><br>";

                $total_used_tokens += 1;
                $needChars = 6000 - 1;
                $random_text = Str::random($needChars);
                echo 'data: '.$output.'/**'.$random_text."\n\n";
                ob_flush();
                flush();
                usleep(500);
            }

            // RSS Thumbnail
            if ($rss_image) {

                $contents = file_get_contents($rss_image);
                $nameOfImage = 'rss-'.Str::random(12).'.jpg';

                //save file on local storage or aws s3
                Storage::disk('public')->put($nameOfImage, $contents);
                $path = '/uploads/'.$nameOfImage;
                $uploadedFile = new File(substr($path, 1));

                if (SettingTwo::first()->ai_image_storage == 's3') {
                    try {
                        $aws_path = Storage::disk('s3')->put('', $uploadedFile);
                        unlink(substr($path, 1));
                        $path = Storage::disk('s3')->url($aws_path);
                    } catch (\Exception $e) {
                        return response()->json(['status' => 'error', 'message' => 'AWS Error - '.$e->getMessage()]);
                    }
                }

                $output = "<img src=\"$path\" style=\"width:100%\"><br><br>";

                $total_used_tokens += 1;
                $needChars = 6000 - 1;
                $random_text = Str::random($needChars);
                echo 'data: '.$output.'/**'.$random_text."\n\n";
                ob_flush();
                flush();
                usleep(500);
            }

            foreach ($stream as $response) {
                if ($settings->openai_default_model == 'text-davinci-003') {
                    if (isset($response->choices[0]->text)) {
                        $message = $response->choices[0]->text;
                        $messageFix = str_replace(["\r\n", "\r", "\n"], '<br/>', $message);
                        $output .= $messageFix;
                        $responsedText .= $message;
                        $total_used_tokens += countWords($messageFix);

                        $string_length = Str::length($messageFix);
                        $needChars = 6000 - $string_length;
                        $random_text = Str::random($needChars);
                        echo 'data: '.$messageFix.'/**'.$random_text."\n\n";
                        ob_flush();
                        flush();
                        usleep(500);
                    }
                } else {
                    if (isset($response['choices'][0]['delta']['content'])) {
                        $message = $response['choices'][0]['delta']['content'];
                        $messageFix = str_replace(["\r\n", "\r", "\n"], '<br/>', $message);
                        $output .= $messageFix;
                        $responsedText .= $message;
                        $total_used_tokens += countWords($messageFix);

                        $string_length = Str::length($messageFix);
                        $needChars = 6000 - $string_length;
                        $random_text = Str::random($needChars);

                        echo 'data: '.$messageFix.'/**'.$random_text."\n\n";
                        ob_flush();
                        flush();
                        usleep(500);
                    }
                }

                if (connection_aborted()) {
                    break;
                }
            }

            $message = UserOpenai::whereId($message_id)->first();
            $message->response = $responsedText;
            $message->output = $output;
            $message->hash = Str::random(256);
            $message->credits = $total_used_tokens;
            $message->words = 0;
            $message->save();

            $user = Auth::user();

            userCreditDecreaseForWord($user, $total_used_tokens);

            echo 'data: [DONE]';
            echo "\n\n";
            ob_flush();
            flush();
            usleep(50000);
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
    }

    public function textOutput($prompt, $post, $creativity, $maximum_length, $number_of_results, $user)
    {
        $user = Auth::user();

        if ($user->remaining_words <= 0 and $user->remaining_words != -1) {
            $data = [
                'errors' => ['You have no credits left. Please consider upgrading your plan.'],
            ];

            return response()->json($data, 419);
        }
        $entry = new UserOpenai();
        $entry->team_id = $user->team_id;
        $entry->title = request('title') ?: __('New Workbook');
        $entry->slug = str()->random(7).str($user->fullName())->slug().'-workbook';
        $entry->user_id = Auth::id();
        $entry->openai_id = $post->id;
        $entry->input = $prompt;
        $entry->response = null;
        $entry->output = null;
        $entry->hash = str()->random(256);
        $entry->credits = 0;
        $entry->words = 0;
        $entry->save();

        $message_id = $entry->id;
        $workbook = $entry;
        $inputPrompt = $prompt;
        $html = view('panel.user.openai.documents_workbook_textarea', compact('workbook'))->render();

        return response()->json(compact('message_id', 'html', 'creativity', 'maximum_length', 'number_of_results', 'inputPrompt'));
    }

    public function codeOutput($prompt, $post, $user)
    {
        if ($this->settings->openai_default_model == 'text-davinci-003') {
            $response = FacadesOpenAI::completions()->create([
                'model' => $this->settings->openai_default_model,
                'prompt' => $prompt,
                'max_tokens' => (int) $this->settings->openai_max_output_length,
            ]);
        } else {
            $response = FacadesOpenAI::chat()->create([
                'model' => $this->settings->openai_default_model,
                'messages' => [
                    ['role' => 'user', 'content' => $prompt],
                ],
            ]);
        }

        $total_used_tokens = $response->usage->totalTokens;

        $entry = new UserOpenai();
        $entry->team_id = $user->team_id;
        $entry->title = request('title') ?: __('New Workbook');
        $entry->slug = Str::random(7).Str::slug($user->fullName()).'-workbook';
        $entry->user_id = Auth::id();
        $entry->openai_id = $post->id;
        $entry->input = $prompt;
        $entry->response = json_encode($response->toArray());

        if ($this->settings->openai_default_model == 'text-davinci-003') {
            $entry->output = $response['choices'][0]['text'];
            $total_used_tokens = countWords($entry->output);
        } else {
            $entry->output = $response->choices[0]->message->content;
            $total_used_tokens = countWords($entry->output);
        }

        $entry->hash = Str::random(256);
        $entry->credits = $total_used_tokens;
        $entry->words = 0;
        $entry->save();

        $user = Auth::user();

        userCreditDecreaseForWord($user, $total_used_tokens);

        return $this->finalizeOutput($post, $entry);
    }

    public function chatImageOutput(Request $request)
    {
        $user = Auth::user();
        // check daily limit
        $chkLmt = Helper::checkImageDailyLimit();
        if ($chkLmt->getStatusCode() === 429) {
            return $chkLmt;
        }
        // check remainings
        $chkImg = Helper::checkRemainingImages($user);
        if ($chkImg->getStatusCode() === 429) {
            return $chkImg;
        }

        $prompt = $request->input('prompt');
        $history = $request->input('chatHistory');

        if ($this->settings?->user_api_option) {
            $apiKeys = explode(',', auth()->user()?->api_keys);
        } else {
            $apiKeys = explode(',', $this->settings?->openai_api_secret);
        }
        $openaiKey = $apiKeys[array_rand($apiKeys)];

        $client = OpenAI::factory()
            ->withApiKey($openaiKey)
            ->withHttpClient(new \GuzzleHttp\Client())
            ->make();

        $completion = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [[
                'role' => 'user',
                'content' => "Write what does user want to draw at the last moment of chat history. \n\n\nChat History: $history \n\n\n\n Result is 'Draw an image of ... ",
            ]],
        ]);

        $path = '';
        $settings = Setting::first();
        // Fetch the Site Settings object with openai_api_secret
        if ($this->settings?->user_api_option) {
            $apiKeys = explode(',', auth()->user()?->api_keys);
        } else {
            $apiKeys = explode(',', $this->settings?->openai_api_secret);
        }
        $apiKey = $apiKeys[array_rand($apiKeys)];
        config(['openai.api_key' => $apiKey]);
        set_time_limit(120);

        $nameOfImage = Str::random(12).'.png';
        $response = FacadesOpenAI::images()->create([
            'model' => 'dall-e-3',
            'prompt' => $completion->choices[0]->message->content,
            'size' => '1024x1024',
            'response_format' => 'b64_json',
        ]);
        $image_url = $response['data'][0]['b64_json'];
        $contents = base64_decode($image_url);

        //save file on local storage or aws s3
        Storage::disk('public')->put($nameOfImage, $contents);
        $path = '/uploads/'.$nameOfImage;
        $uploadedFile = new File(substr($path, 1));

        if (SettingTwo::first()->ai_image_storage == 's3') {
            try {
                $aws_path = Storage::disk('s3')->put('', $uploadedFile);
                unlink(substr($path, 1));
                $path = Storage::disk('s3')->url($aws_path);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'AWS Error - '.$e->getMessage()]);
            }
        }

        // if in team
        if ($user->getAttribute('team')) {
            $teamManager = $user->teamManager;
            if ($teamManager) {
                if ($teamManager->remaining_images != -1) {
                    $teamManager->remaining_images -= 1;
                    $teamManager->save();
                }
                if ($teamManager->remaining_images < -1) {
                    $teamManager->remaining_images = 0;
                    $teamManager->save();
                }
            }
            $member = $user->teamMember;
            if ($member) {
                if (! $member->allow_unlimited_credits) {
                    if ($member->remaining_images != -1) {
                        $member->remaining_images -= 1;
                        $member->save();
                    }
                    if ($member->remaining_images < -1) {
                        $member->remaining_images = 0;
                        $member->save();
                    }
                }
                $member->used_image_credit += 1;
                $member->save();
            }
        } else {
            if ($user->remaining_images != -1) {
                $user->remaining_images -= 1;
                $user->save();
            }
            if ($user->remaining_images < -1) {
                $user->remaining_images = 0;
                $user->save();
            }
        }
		Usage::getSingle()->updateImageCounts(1);
        return response()->json(['path' => $path]);
    }

    public function imageOutput($param, $post, $user)
    {
        $user = Auth::user();
        // check daily limit
        $chkLmt = Helper::checkImageDailyLimit();
        if ($chkLmt->getStatusCode() === 429) {
            return $chkLmt;
        }
        // check remainings
        $chkImg = Helper::checkRemainingImages($user);
        if ($chkImg->getStatusCode() === 429) {
            return $chkImg;
        }

        if ($this->settings?->user_api_option) {
            $apiKeys = explode(',', auth()->user()?->api_keys);
        } else {
            $apiKeys = explode(',', $this->settings?->openai_api_secret);
        }
        $apiKey = $apiKeys[array_rand($apiKeys)];
        config(['openai.api_key' => $apiKey]);
        set_time_limit(120);

        //save generated image datas
        $entries = [];
        $prompt = '';
        $image_generator = $param['image_generator'];
        $number_of_images = (int) $param['image_number_of_images'];
        $mood = $param['image_mood'];

        if ($image_generator != self::STABLEDIFFUSION) {
            $size = $param['size'];
            $description = $param['description'];
            $prompt = "$description";
            $style = $param['image_style'];
            $lighting = $param['image_lighting'];
            // $image_model = $param['image_model'];

            if ($style != null) {
                $prompt .= ' '.$style.' style.';
            }
            if ($lighting != null) {
                $prompt .= ' '.$lighting.' lighting.';
            }
            if ($mood != null) {
                $prompt .= ' '.$mood.' mood.';
            }
        } else {
            $stable_type = $param['type'];
            $prompt = $param['stable_description'];
            $negative_prompt = $param['negative_prompt'];
            $style_preset = $param['style_preset'];
            $sampler = $param['sampler'];
            $clip_guidance_preset = $param['clip_guidance_preset'];
            $image_resolution = $param['image_resolution'];
            $init_image = $param['image_src'] ?? null;
        }

        $image_storage = $this->settings_two->ai_image_storage;

        for ($i = 0; $i < $number_of_images; $i++) {
            if ($image_generator != self::STABLEDIFFUSION) {
                //send prompt to openai
                if ($prompt == null) {
                    return response()->json(['status' => 'error', 'message' => 'You must provide a prompt']);
                }
                if ($this->settings_two->dalle == 'dalle2') {
                    $model = 'dall-e-2';
                    $demosize = '256x256'; // smallest size for demo
                } elseif ($this->settings_two->dalle == 'dalle3') {
                    $model = 'dall-e-3';
                    $demosize = '1024x1024'; // smallest size for demo
                } else {
                    $model = 'dall-e-2';
                    $demosize = '256x256'; // smallest size for demo
                }
                $quality = $param['quality'];
                $response = FacadesOpenAI::images()->create([
                    'model' => $model,
                    'prompt' => $prompt,
                    'size' => Helper::appIsDemo() ? $demosize : $size,
                    'response_format' => 'b64_json',
                    'quality' => Helper::appIsDemo() ? 'standard' : $quality,
                    'n' => 1,
                ]);
                $image_url = $response['data'][0]['b64_json'];
                $contents = base64_decode($image_url);

                $nameprompt = mb_substr($prompt, 0, 15);
                $nameprompt = explode(' ', $nameprompt)[0];

                $nameOfImage = Str::random(12).'-DALL-E-'.Str::slug($nameprompt).'.png';

                //save file on local storage or aws s3
                Storage::disk('public')->put($nameOfImage, $contents);
                $path = 'uploads/'.$nameOfImage;
            } else {
                //send prompt to stablediffusion
                $settings = SettingTwo::first();
                $stablediffusionKeys = explode(',', $settings->stable_diffusion_api_key);
                $stablediffusionKey = $stablediffusionKeys[array_rand($stablediffusionKeys)];
                if ($prompt == null) {
                    return response()->json(['status' => 'error', 'message' => 'You must provide a prompt']);
                }
                if ($stablediffusionKey == '') {
                    return response()->json(['status' => 'error', 'message' => 'You must provide a StableDiffusion API Key.']);
                }
                $width = intval(explode('x', $image_resolution)[0]);
                $height = intval(explode('x', $image_resolution)[1]);
                $client = new Client([
                    'base_uri' => 'https://api.stability.ai/v1/generation/',
                    'headers' => [
                        'content-type' => ($stable_type == 'upscale' || $stable_type == 'image-to-image') ? 'multipart/form-data' : 'application/json',
                        'Authorization' => 'Bearer '.$stablediffusionKey,
                    ],
                ]);

                // Stablediffusion engine
                $engine = $this->settings_two->stablediffusion_default_model;
                // Content Type
                $content_type = 'json';

                $payload = [
                    'cfg_scale' => 7,
                    'clip_guidance_preset' => $clip_guidance_preset ?? 'NONE',
                    'samples' => 1,
                    'steps' => 50,
                ];

                if ($sampler) {
                    $payload['sampler'] = $sampler;
                }

                if ($style_preset) {
                    $payload['style_preset'] = $style_preset;
                }

                switch ($stable_type) {
                    case 'multi-prompt':
                        $stable_url = 'text-to-image';
                        $payload['width'] = $width;
                        $payload['height'] = $height;
                        $arr = [];
                        foreach ($prompt as $p) {
                            $arr[] = [
                                'text' => $p.($mood == null ? '' : (' '.$mood.' mood.')),
                                'weight' => 1,
                            ];
                        }
                        $prompt = $arr;
                        break;
                    case 'upscale':
                        $stable_url = 'image-to-image/upscale';
                        $engine = 'esrgan-v1-x2plus';
                        $payload = [];
                        $payload['image'] = $init_image->get();
                        $prompt = [
                            [
                                'text' => $prompt.'-'.Str::random(16),
                                'weight' => 1,
                            ],
                        ];
                        $content_type = 'multipart';
                        break;
                    case 'image-to-image':
                        $stable_url = $stable_type;
                        $payload['init_image'] = $init_image->get();
                        $prompt = [
                            [
                                'text' => $prompt.($mood == null ? '' : (' '.$mood.' mood.')),
                                'weight' => 1,
                            ],
                        ];
                        $content_type = 'multipart';
                        break;
                    default:
                        $stable_url = $stable_type;
                        $payload['width'] = $width;
                        $payload['height'] = $height;
                        $prompt = [
                            [
                                'text' => $prompt.($mood == null ? '' : (' '.$mood.' mood.')),
                                'weight' => 1,
                            ],
                        ];
                        break;
                }

                if ($negative_prompt) {
                    $prompt[] = ['text' => $negative_prompt, 'weight' => -1];
                }

                if ($stable_type != 'upscale') {
                    $payload['text_prompts'] = $prompt;
                }

                if ($content_type == 'multipart') {
                    $multipart = [];
                    foreach ($payload as $key => $value) {
                        if (! is_array($value)) {
                            $multipart[] = ['name' => $key, 'contents' => $value];

                            continue;
                        }

                        foreach ($value as $multiKey => $multiValue) {
                            $multiName = $key.'['.$multiKey.']'.(is_array($multiValue) ? '['.key($multiValue).']' : '').'';
                            $multipart[] = ['name' => $multiName, 'contents' => (is_array($multiValue) ? reset($multiValue) : $multiValue)];
                        }
                    }
                    $payload = $multipart;
                }

                try {
                    $response = $client->post("$engine/$stable_url", [
                        $content_type => $payload,
                    ]);
                } catch (RequestException $e) {
                    if ($e->hasResponse()) {
                        $response = $e->getResponse();
                        $statusCode = $response->getStatusCode();
                        // Custom handling for specific status codes here...

                        if ($statusCode == '404') {
                            // Handle a not found error
                        } elseif ($statusCode == '500') {
                            // Handle a server error
                        }

                        $errorMessage = $response->getBody()->getContents();

                        return response()->json(['status' => 'error', 'message' => json_decode($errorMessage)->message]);
                        // Log the error message or handle it as required
                    }

                    return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
                } catch (Exception $e) {
                    if ($e->hasResponse()) {
                        $response = $e->getResponse();
                        $statusCode = $response->getStatusCode();
                        // Custom handling for specific status codes here...

                        if ($statusCode == '404') {
                            // Handle a not found error
                        } elseif ($statusCode == '500') {
                            // Handle a server error
                        }

                        $errorMessage = $response->getBody()->getContents();

                        return response()->json(['status' => 'error', 'message' => json_decode($errorMessage)->message]);
                        // Log the error message or handle it as required
                    }

                    return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
                }
                $body = $response->getBody();
                if ($response->getStatusCode() == 200) {

                    $nameprompt = mb_substr($prompt[0]['text'], 0, 15);
                    $nameprompt = explode(' ', $nameprompt)[0];

                    $nameOfImage = Str::random(12).'-DALL-E-'.$nameprompt.'.png';

                    $contents = base64_decode(json_decode($body)->artifacts[0]->base64);
                } else {
                    $message = '';
                    if ($body->status == 'error') {
                        $message = $body->message;
                    } else {
                        $message = 'Failed, Try Again';
                    }

                    return response()->json(['status' => 'error', 'message' => $message]);
                }

                Storage::disk('public')->put($nameOfImage, $contents);
                $path = 'uploads/'.$nameOfImage;
            }
            if ($image_storage == self::STORAGE_S3) {
                try {
                    $uploadedFile = new File($path);
                    $aws_path = Storage::disk('s3')->put('', $uploadedFile);
                    unlink($path);
                    $path = Storage::disk('s3')->url($aws_path);
                } catch (\Exception $e) {
                    return response()->json(['status' => 'error', 'message' => 'AWS Error - '.$e->getMessage()]);
                }
            }
            $entry = new UserOpenai();
            $entry->team_id = $user->team_id;
            $entry->title = request('title') ?: __('New Image');
            $entry->slug = Str::random(7).Str::slug($user->fullName()).'-workbsook';
            $entry->user_id = Auth::id();
            $entry->openai_id = $post->id;
            $entry->input = $prompt;
            if ($image_generator == self::STABLEDIFFUSION) {
                $entry->input = $prompt[0]['text'];
            } else {
                $entry->input = $prompt;
            }
            // $entry->input = $prompt[0]['text'];
            $entry->response = $image_generator == 'stablediffusion' ? 'SD' : 'DE';
            $entry->output = $image_storage == self::STORAGE_S3 ? $path : '/'.$path;
            $entry->hash = Str::random(256);
            $entry->credits = 1;
            $entry->words = 0;
            $entry->storage = $image_storage == self::STORAGE_S3 ? UserOpenai::STORAGE_AWS : UserOpenai::STORAGE_LOCAL;
            $entry->payload = request()->all();
            $entry->save();

            //push each generated image to an array
            array_push($entries, $entry);

            // if in team
            if ($user->getAttribute('team')) {
                $teamManager = $user->teamManager;
                if ($teamManager) {
                    if ($teamManager->remaining_images != -1) {
                        $teamManager->remaining_images -= 1;
                        $teamManager->save();
                    }
                    if ($teamManager->remaining_images < -1) {
                        $teamManager->remaining_images = 0;
                        $teamManager->save();
                    }
                }
                $member = $user->teamMember;
                if ($member) {
                    if (! $member->allow_unlimited_credits) {
                        if ($member->remaining_images != -1) {
                            $member->remaining_images -= 1;
                            $member->save();
                        }
                        if ($member->remaining_images < -1) {
                            $member->remaining_images = 0;
                            $member->save();
                        }
                    }
                    $member->used_image_credit += 1;
                    $member->save();
                }
            } else {
                if ($user->remaining_images != -1) {
                    $user->remaining_images -= 1;
                    $user->save();
                }
                if ($user->remaining_images < -1) {
                    $user->remaining_images = 0;
                    $user->save();
                }
            }

			Usage::getSingle()->updateImageCounts(1);
        }

        return response()->json(['status' => 'success', 'images' => $entries, 'image_storage' => $image_storage]);
    }

    public function videoOutput($param, $post, $user)
    {
        $user = Auth::user();
        // check daily limit
        $chkLmt = Helper::checkImageDailyLimit();
        if ($chkLmt->getStatusCode() === 429) {
            return $chkLmt;
        }
        // check remainings
        $chkImg = Helper::checkRemainingImages($user);
        if ($chkImg->getStatusCode() === 429) {
            return $chkImg;
        }

        set_time_limit(120);

        $init_image = file_get_contents($param['image_src']);
        Log::info($init_image);

        $nameOfImage = Str::random(12).'.png';
        Log::info($nameOfImage);
        Storage::disk('public')->put($nameOfImage, $init_image);
        $path = '/uploads/'.$nameOfImage;
        $uploadedFile = new File(substr($path, 1));

        if (SettingTwo::first()->ai_image_storage == 's3') {
            try {
                $aws_path = Storage::disk('s3')->put('', $uploadedFile);
                unlink(substr($path, 1));
                $path = Storage::disk('s3')->url($aws_path);
            } catch (\Exception $e) {
                return response()->json(['status' => 'error', 'message' => 'AWS Error - '.$e->getMessage()]);
            }
        }

        $seed = $param['seed'];
        $cfg_scale = $param['cfg_scale'];
        $motion_bucket_id = $param['motion_bucket_id'];

        $image_storage = $this->settings_two->ai_image_storage;

        //send prompt to stablediffusion
        $settings = SettingTwo::first();
        $stablediffusionKeys = explode(',', $settings->stable_diffusion_api_key);
        $stablediffusionKey = $stablediffusionKeys[array_rand($stablediffusionKeys)];
        if ($stablediffusionKey == '') {
            return response()->json(['status' => 'error', 'message' => 'You must provide a StableDiffusion API Key.']);
        }

        $client = new Client([
            'base_uri' => 'https://api.stability.ai/v2alpha/generation/',
            'headers' => [
                'content-type' => 'multipart/form-data',
                'Authorization' => 'Bearer '.$stablediffusionKey,
            ],
        ]);

        $payload = [
            'image' => $init_image,
            'cfg_scale' => $cfg_scale,
            'seed' => $seed,
            'motion_bucket_id' => $motion_bucket_id,
        ];

        $multipart = [];
        foreach ($payload as $key => $value) {
            if ($key == 'image') {
                $multipart[] = ['name' => $key, 'contents' => $value, 'filename' => 'image.png'];
            } else {
                $multipart[] = ['name' => $key, 'contents' => $value];
            }
        }
        $payload = $multipart;

        try {
            $response = $client->post('image-to-video', [
                'multipart' => $payload,
            ]);
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                // Custom handling for specific status codes here...

                if ($statusCode == '404') {
                    // Handle a not found error
                } elseif ($statusCode == '500') {
                    // Handle a server error
                }

                $errorMessage = $response->getBody()->getContents();

                return response()->json(['status' => 'error', 'message' => json_decode($errorMessage)->message]);
                // Log the error message or handle it as required
            }

            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        } catch (Exception $e) {
            if ($e->hasResponse()) {
                $response = $e->getResponse();
                $statusCode = $response->getStatusCode();
                // Custom handling for specific status codes here...

                if ($statusCode == '404') {
                    // Handle a not found error
                } elseif ($statusCode == '500') {
                    // Handle a server error
                }

                $errorMessage = $response->getBody()->getContents();

                return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
                // Log the error message or handle it as required
            }

            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
        $body = $response->getBody();
        if ($response->getStatusCode() == 200) {
            return response()->json(['status' => 'success', 'id' => json_decode($body)->id, 'sourceUrl' => $path]);
        } else {
            $message = '';
            if ($body->status == 'error') {
                $message = $body->message;
            } else {
                $message = 'Failed, Try Again';
            }

            return response()->json(['status' => 'error', 'message' => $message]);
        }
    }

    public function checkVideoProgress(Request $request)
    {
        $resultId = $request->id;
        $user = Auth::user();

        $client = new Client();
        $settings = SettingTwo::first();
        $stablediffusionKeys = explode(',', $settings->stable_diffusion_api_key);
        $stablediffusionKey = $stablediffusionKeys[array_rand($stablediffusionKeys)];

        $client = new Client([
            'base_uri' => 'https://api.stability.ai/v2alpha/generation/image-to-video/result/'.$resultId,
            'headers' => [
                'Accept' => 'video/*',
                'Authorization' => 'Bearer '.$stablediffusionKey,
            ],
        ]);

        try {
            $response = $client->request('GET');
            if ($response->getStatusCode() == 200) {
                $fileContents = $response->getBody()->getContents();
                $nameOfImage = 'image-to-video-'.Str::random(12).'.mp4';
                Storage::disk('public')->put($nameOfImage, $fileContents);
                $path = 'uploads/'.$nameOfImage;

                $image_storage = $this->settings_two->ai_image_storage;
                if ($image_storage == self::STORAGE_S3) {
                    try {
                        $uploadedFile = new File($path);
                        $aws_path = Storage::disk('s3')->put('', $uploadedFile);
                        unlink($path);
                        $path = Storage::disk('s3')->url($aws_path);
                    } catch (\Exception $e) {
                        return response()->json(['status' => 'error', 'message' => 'AWS Error - '.$e->getMessage()]);
                    }
                }

                $entry = new UserOpenai();
                $entry->team_id = $user->team_id;
                $entry->title = __('New Video');
                $entry->slug = Str::random(7).Str::slug($user->fullName()).'-workbsook';
                $entry->user_id = Auth::id();
                $entry->openai_id = OpenAIGenerator::where('slug', 'ai_video')->first()->id;
                $entry->input = $request->url;
                $entry->response = 'VIDEO';
                $entry->output = $image_storage == self::STORAGE_S3 ? $path : '/'.$path;
                $entry->hash = Str::random(256);
                $entry->credits = 5;
                $entry->words = 0;
                $entry->storage = $image_storage == self::STORAGE_S3 ? UserOpenai::STORAGE_AWS : UserOpenai::STORAGE_LOCAL;
                $entry->payload = request()->all();
                $entry->save();

                //push each generated image to an array

                // if in team
                if ($user->getAttribute('team')) {
                    $teamManager = $user->teamManager;
                    if ($teamManager) {
                        if ($teamManager->remaining_images != -1) {
                            $teamManager->remaining_images -= 1;
                            $teamManager->save();
                        }
                        if ($teamManager->remaining_images < -1) {
                            $teamManager->remaining_images = 0;
                            $teamManager->save();
                        }
                    }
                    $member = $user->teamMember;
                    if ($member) {
                        if (! $member->allow_unlimited_credits) {
                            if ($member->remaining_images != -1) {
                                $member->remaining_images -= 1;
                                $member->save();
                            }
                            if ($member->remaining_images < -1) {
                                $member->remaining_images = 0;
                                $member->save();
                            }
                        }
                        $member->used_image_credit += 1;
                        $member->save();
                    }
                } else {
                    if ($user->remaining_images != -1) {
                        $user->remaining_images -= 1;
                        $user->save();
                    }
                    if ($user->remaining_images < -1) {
                        $user->remaining_images = 0;
                        $user->save();
                    }
                }

				Usage::getSingle()->updateImageCounts(5);

                return response()->json(['status' => 'success', 'status' => 'finished', 'url' => $path, 'video' => $entry]);
            } elseif ($response->getStatusCode() == 202) {
                return response()->json(['status' => 'success', 'status' => 'in-progress']);
            }
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function audioOutput($file, $post, $user)
    {

        $path = 'upload/audio/';

        $file_name = Str::random(4).'-'.Str::slug($user->fullName()).'-audio.'.$file->getClientOriginalExtension();

        //Audio Extension Control
        $imageTypes = ['mp3', 'mp4', 'mpeg', 'mpga', 'm4a', 'wav', 'webm'];
        if (! in_array(Str::lower($file->getClientOriginalExtension()), $imageTypes)) {
            $data = [
                'errors' => ['Invalid extension, accepted extensions are mp3, mp4, mpeg, mpga, m4a, wav, and webm.'],
            ];

            return response()->json($data, 419);
        }

        $file->move($path, $file_name);

        $response = FacadesOpenAI::audio()->transcribe([
            'file' => fopen($path.$file_name, 'r'),
            'model' => 'whisper-1',
            'response_format' => 'verbose_json',
        ]);

        $text = $response->text;

        $entry = new UserOpenai();
        $entry->team_id = $user->team_id;
        $entry->title = request('title') ?: __('New Workbook');
        $entry->slug = Str::random(7).Str::slug($user->fullName()).'-speech-to-text-workbook';
        $entry->user_id = Auth::id();
        $entry->openai_id = $post->id;
        $entry->input = $path.$file_name;
        $entry->response = json_encode($response->toArray());
        $entry->output = $text;
        $entry->hash = Str::random(256);
        $entry->credits = countWords($text);
        $entry->words = countWords($text);
        $entry->save();

        $team = $user->getAttribute('team');

        userCreditDecreaseForWord($user, countWords($text));

        //Workbook add-on
        $workbook = $entry;

        $userOpenai = UserOpenai::where('user_id', Auth::id())->where('openai_id', $post->id)->orderBy('created_at', 'desc')->get();
        $openai = OpenAIGenerator::find($post->id);
        $html2 = view('panel.user.openai.components.generator_sidebar_table', compact('userOpenai', 'openai'))->render();

        return response()->json(compact('html2'));
    }

    public function finalizeOutput($post, $entry)
    {
        //Workbook add-on
        $workbook = $entry;
        $html = view('panel.user.openai.documents_workbook_textarea', compact('workbook'))->render();
        $userOpenai = UserOpenai::where('user_id', Auth::id())->where('openai_id', $post->id)->orderBy('created_at', 'desc')->get();
        $openai = OpenAIGenerator::find($post->id);
        $html2 = view('panel.user.openai.components.generator_sidebar_table', compact('userOpenai', 'openai'))->render();

        return response()->json(compact('html', 'html2'));
    }

    public function messageTitleSave(Request $request)
    {
        if (! $request['message_id'] || ! $request['title']) {
            return response()->json([
                'message' => trans('TItle is required'),
            ]);
        }

        $entry = UserOpenai::find($request->message_id);
        $entry->title = request('title');
        $entry->save();

        return response()->json([
            'message' => trans('Title update'),
        ]);
    }

    public function lowGenerateSave(Request $request)
    {
        $response = $request->response;
        $total_user_tokens = countWords($response);

        $entry = UserOpenai::find($request->message_id);
        if (request('title')) {
            $entry->title = request('title');
        }
        $entry->credits = $total_user_tokens;
        $entry->words = $total_user_tokens;
        $entry->response = $response;
        $entry->output = $response;
        $entry->save();

        $user = Auth::user();

        userCreditDecreaseForWord($user, $total_user_tokens);
    }

    public function stream(Request $request)
    {
        if ($this->settings?->user_api_option) {
            $apiKeys = explode(',', auth()->user()?->api_keys);
        } else {
            $apiKeys = explode(',', $this->settings?->openai_api_secret);
        }
        $openaiKey = $apiKeys[array_rand($apiKeys)];

        $openai_model = Setting::first()?->openai_default_model;

        $client = OpenAI::factory()
            ->withApiKey($openaiKey)
            ->make();

        session_start();
        header('Content-type: text/event-stream');
        header('Cache-Control: no-cache');
        ob_end_flush();
        $result = $client->chat()->createStreamed([
            'model' => $openai_model,
            'messages' => [[
                'role' => 'user',
                'content' => $request->get('message'),
            ]],
            'stream' => true,
        ]);

        foreach ($result as $response) {
            echo "event: data\n";
            echo 'data: '.json_encode(['message' => $response->choices[0]->delta->content])."\n\n";
            flush();
        }

        echo "event: stop\n";
        echo "data: stopped\n\n";
    }

    public function chatStreamOld(Request $request)
    {
        $openaiKey = $this->settings->user_api_option ? explode(',', auth()->user()->api_keys) : explode(',', $this->settings->openai_api_secret);
		$openaiKey = $openaiKey[array_rand($openaiKey)];
		$openai_model = $this->settings->openai_default_model;

        $client = OpenAI::factory()
            ->withApiKey($openaiKey)
            ->withHttpClient(new \GuzzleHttp\Client())
            ->make();

		header('Content-type: text/event-stream');
        header('Cache-Control: no-cache');
        ob_end_flush();

        $vectorService = new VectorService();
        //Add previous chat history to the prompt
        $prompt = $request->get('message');
        $realtime = $request->get('realtime');
        $categoryId = $request->get('category');
        $realtimePrompt = $prompt;
        $extra_prompt = $vectorService->getMostSimilarText($prompt, $request->chat_id);

        $type = $request->get('type');
        $list = UserOpenaiChat::where('user_id', Auth::id())->where('openai_chat_category_id', $categoryId)->orderBy('updated_at', 'desc');
        $chat = $list->first();
        $category = $chat->category;
        
		$prefix = '';
        if ($category->prompt_prefix != null) {
            $prefix = "$category->prompt_prefix you will now play a character and respond as that character (You will never break character). Your name is $category->human_name but do not introduce by yourself as well as greetings.";
        }

        $messages = [[
            'role' => 'assistant',
            'content' => $prefix,
        ]];

       	$lastThreeMessage = $chat != null ? $chat->messages()->whereNotNull('input')->orderBy('created_at', 'desc')->take(3)->get()->reverse() : null;

        if ($category->chat_completions == null) {
            $category->chat_completions = '[]';
        }
		if ($lastThreeMessage) {
			foreach ($lastThreeMessage as $entry) {
				if ($entry->output == null) {
					$entry->output = '';
				}
				array_push($messages, [
					'role' => 'user',
					'content' => $entry->input ?? '',
				]);
				array_push($messages, [
					'role' => 'assistant',
					'content' => $entry->output ?? '',
				]);
				$messages = array_merge($messages, json_decode($category->chat_completions, true));
			}
		}

        if ($extra_prompt == '') {
            if ($realtime == 1 && $this->settings_two?->serper_api_key != null) {
                $clientt = new \GuzzleHttp\Client();
                $headers = [
                    'X-API-KEY' => $this->settings_two?->serper_api_key,
                    'Content-Type' => 'application/json',
                ];
                $body = [
                    'q' => $realtimePrompt,
                ];
                $response = $clientt->post('https://google.serper.dev/search', [
                    'headers' => $headers,
                    'json' => $body,
                ]);
                $toGPT = $response->getBody()->getContents();
                $final_prompt =
                    'Prompt: '.$realtimePrompt.
                    '\n\nWeb search json results: '
                    .json_encode($toGPT).
                    '\n\nInstructions: Based on the Prompt generate a proper response with help of Web search results(if the Web search results in the same context). Only if the prompt require links: (make curated list of links and descriptions using only the <a target="_blank">, write links with using <a target="_blank"> with mrgin Top of <a> tag is 5px and start order as number and write link first and then write description). Must not write links if its not necessary. Must not mention anything about the prompt text.';
                // unset($history);
                array_push($messages, [
                    'role' => 'user',
                    'content' => $final_prompt,
                ]);
            } else {
                array_push($messages, [
                    'role' => 'user',
                    'content' => $prompt,
                ]);
            }
        } else {
            array_push($messages, [
                'role' => 'user',
                'content' => "'this file' means file content. Must not reference previous chats if user asking about pdf. Must reference file content if only user is asking about file content. Else just response as an assistant shortly and professionaly without must not referencing file content. \n\n\n\n\nUser qusetion: $prompt \n\n\n\n\n Document Content: \n $extra_prompt",
            ]);
        }
        //send request to openai
		if ($type == 'chat') {
			$result = $client->chat()->createStreamed([
				'model' => $openai_model,
				'messages' => $messages,
			]);
			foreach ($result as $response) {
				echo "event: data\n";
				echo 'data: '.json_encode(['message' => $response->choices[0]->delta->content])."\n\n";
				flush();
			}
		} elseif ($type == 'vision') {
			$images = json_decode($request->get('images'), true);

			$gclient = new Client();

			if ($this->settings?->user_api_option) {
				$apiKeys = explode(',', auth()->user()?->api_keys);
			} else {
				$apiKeys = explode(',', $this->settings?->openai_api_secret);
			}
			$openaiApiKey = $apiKeys[array_rand($apiKeys)];
			$url = 'https://api.openai.com/v1/chat/completions';

			$response = $gclient->post(
				$url,
				[
					'headers' => [
						'Authorization' => 'Bearer '.$openaiApiKey,
					],
					'json' => [
						'model' => 'gpt-4-vision-preview',
						'messages' => [
							[
								'role' => 'user',
								'content' => array_merge(
									[
										[
											'type' => 'text',
											'text' => $prompt,
										],
									],
									collect($images)->map(function ($item) {
										if (Str::startsWith($item, 'http')) {
											$imageData = file_get_contents($item);
										} else {
											$imageData = file_get_contents(substr($item, 1, strlen($item) - 1));
										}
										$base64Image = base64_encode($imageData);

										return [
											'type' => 'image_url',
											'image_url' => [
												'url' => 'data:image/png;base64,'.$base64Image,
											],
										];
									})->toArray()
								),
							],
						],
						'max_tokens' => 2000,
						'stream' => true,
					],
				],
			);

			foreach (explode("\n", $response->getBody()->getContents()) as $chunk) {
				if (strlen($chunk) > 5 && $chunk != 'data: [DONE]' && isset(json_decode(substr($chunk, 6, strlen($chunk) - 6))->choices[0]->delta->content)) {
					echo "event: data\n";
					echo 'data: '.json_encode(['message' => json_decode(substr($chunk, 6, strlen($chunk) - 6))->choices[0]->delta->content])."\n\n";
					flush();
				}
			}
		}

		echo "event: stop\n";
		echo "data: stopped\n\n";
    }

	public function chatStreamOld2(Request $request)
    {
		$openaiKey = $this->settings->user_api_option ? explode(',', auth()->user()->api_keys) : explode(',', $this->settings->openai_api_secret);
		$openaiApiKey = $openaiKey[array_rand($openaiKey)];
		$opclient = OpenAI::factory()
		->withApiKey($openaiApiKey)
		->withHttpClient(new \GuzzleHttp\Client())
		->make();
		
		$type = $request->get('type');
		$chat_id = $request->get('chat_id');
		$prompt = $request->get('message');
        $realtime = $request->get('realtime');
        $categoryId = $request->get('category');
		$user = Auth::user();
		$userId = $user->id;
		$history = [];

		$chat_bot = $this->settings?->openai_default_model;
		$chat_bot == null ? 'gpt-3.5-turbo': $chat_bot;
		$chat_bot == 'gpt-4-vision-preview' ? $chat_bot = 'gpt-4-1106-preview' : $chat_bot;
        $realtimePrompt = $prompt;

        $chat = UserOpenaiChat::whereId($chat_id)->first();
		$category = $chat->category;
		if ($category->chat_completions == null) {
            $category->chat_completions = '[]';
        }
		if($type == 'vision'){
			$history[] = [
				'role' => 'system',
				'content' => "You will now play a character and respond as that character (You will never break character). Your name is Vision AI. Must not introduce by yourself as well as greetings. Help also with asked questions based on previous responses and images if exists and related."
			];
		}
		elseif ($category->chat_completions) {
			$chat_completions = json_decode($category->chat_completions, true);
			foreach ($chat_completions as $item) {
				$history[] = [
					'role' => $item['role'],
					'content' => $item['content'],
				];
			}
		} else {
			$history[] = ['role' => 'system', 'content' => 'You are a helpful assistant.'];
		}
        $lastThreeMessageQuery = $chat->messages()
			->whereNotNull('input')
			->orderBy('created_at', 'desc')
			->take(4)
			->get()
			->reverse();

		$vectorService = new VectorService();
		$extra_prompt = $vectorService->getMostSimilarText($prompt, $chat_id, 5 , $chat->chatbot_id);
		$count = count($lastThreeMessageQuery);
		if ($count > 1) {
			if($realtime && $this->settings_two->serper_api_key != null){	
				if ($extra_prompt != '') {
					$history[] = ['role' => 'user', 'content' => "'this file' means file content. Must not reference previous chats if user asking about pdf. Must reference file content if only user is asking about file content. Else just response as an assistant shortly and professionaly without must not referencing file content. . User: $prompt \n\n\n\n\n Document Content: \n $extra_prompt"];
				} else {
					if($type == 'vision') {
						foreach ($lastThreeMessageQuery as $threeMessage) {
							$history[] = [
								'role' => 'user', 
								'content' => array_merge(
									[
										[
											'type' => 'text',
											'text' => $threeMessage->input,
										],
									],
									collect($threeMessage->images)->map(function ($item) {
										if($item !== "undefined" || $item !== null) {
											if (Str::startsWith($item, 'http')) {
												$imageData = file_get_contents($item);
											} else {
												$imageData = file_get_contents(substr($item, 1, strlen($item) - 1));
											}
											$base64Image = base64_encode($imageData);

											return [
												'type' => 'image_url',
												'image_url' => [
													'url' => 'data:image/png;base64,'.$base64Image,
												],
											];
										}
									})->toArray()
								),
							];
							if ($threeMessage->response != null) {
								$history[] = ['role' => 'assistant', 'content' => $threeMessage->response];
							}
						}
					}else{
						foreach ($lastThreeMessageQuery as $threeMessage) {
							$history[] = ['role' => 'user', 'content' => $threeMessage->input];
							if ($threeMessage->output != null) {
								$history[] = ['role' => 'assistant', 'content' => $threeMessage->output];
							}else{
								$history[] = ['role' => 'assistant', 'content' => ''];
							}
						}
						$history[] = ['role' => 'user', 'content' => $prompt];
					}
					$sclient = new Client();
					$headers = [
						'X-API-KEY' => $this->settings_two->serper_api_key,
						'Content-Type' => 'application/json',
					];
					$body = [
						'q' => $realtimePrompt,
					];
					$response = $sclient->post('https://google.serper.dev/search', [
						'headers' => $headers,
						'json' => $body,
					]);
					$toGPT = $response->getBody()->getContents();
					try {
						$toGPT = json_decode($toGPT);
					} catch (\Throwable $th) {
					}

					$final_prompt =
						'Prompt: '.$realtimePrompt.
						'\n\nWeb search json results: '
						.json_encode($toGPT).
						'\n\nInstructions: Based on the Prompt generate a proper response with help of Web search results(if the Web search results in the same context). Only if the prompt require links: (make curated list of links and descriptions using only the <a target="_blank">, write links with using <a target="_blank"> with mrgin Top of <a> tag is 5px and start order as number and write link first and then write description). Must not write links if its not necessary. Must not mention anything about the prompt text.';
					$history[] = ['role' => 'user', 'content' => $final_prompt];
				}
			}else{
				if ($extra_prompt != '') {
					$lastThreeMessageQuery[$count - 1]->input = "'this file' means file content. Must not reference previous chats if user asking about pdf. Must reference file content if only user is asking about file content. Else just response as an assistant shortly and professionaly without must not referencing file content. \n\n\n\n\nUser qusetion: $prompt \n\n\n\n\n Document Content: \n $extra_prompt";
				}
				if($type == 'vision') {
					foreach ($lastThreeMessageQuery as $threeMessage) {
						$history[] = [
							'role' => 'user', 
							'content' => array_merge(
								[
									[
										'type' => 'text',
										'text' => $threeMessage->input,
									],
								],
								collect($threeMessage->images)->map(function ($item) {
									if($item !== "undefined" || $item !== null) {
										if (Str::startsWith($item, 'http')) {
											$imageData = file_get_contents($item);
										} else {
											$imageData = file_get_contents(substr($item, 1, strlen($item) - 1));
										}
										$base64Image = base64_encode($imageData);

										return [
											'type' => 'image_url',
											'image_url' => [
												'url' => 'data:image/png;base64,'.$base64Image,
											],
										];
									}
								})->toArray()
							),
						];
						if ($threeMessage->response != null) {
							$history[] = ['role' => 'assistant', 'content' => $threeMessage->response];
						}
					}
				}else{
					foreach ($lastThreeMessageQuery as $threeMessage) {
						$history[] = ['role' => 'user', 'content' => $threeMessage->input];
						if ($threeMessage->output != null) {
							$history[] = ['role' => 'assistant', 'content' => $threeMessage->output];
						}else{
							$history[] = ['role' => 'assistant', 'content' => ''];
						}
					}
					$history[] = ['role' => 'user', 'content' => $prompt];
				}
			}
		}else{
			if($realtime && $this->settings_two->serper_api_key != null){
				if ($extra_prompt != '') {
					$history[] = ['role' => 'user', 'content' => "'this file' means file content. Must not reference previous chats if user asking about pdf. Must reference file content if only user is asking about file content. Else just response as an assistant shortly and professionaly without must not referencing file content. . User: $prompt \n\n\n\n\n Document Content: \n $extra_prompt"];
				} else {
					$client = new Client();
					$headers = [
						'X-API-KEY' => $this->settings_two->serper_api_key,
						'Content-Type' => 'application/json',
					];
					$body = [
						'q' => $realtimePrompt,
					];
					$response = $client->post('https://google.serper.dev/search', [
						'headers' => $headers,
						'json' => $body,
					]);
					$toGPT = $response->getBody()->getContents();
					try {
						$toGPT = json_decode($toGPT);
					} catch (\Throwable $th) {
					}

					$final_prompt =
						'Prompt: '.$realtimePrompt.
						'\n\nWeb search json results: '
						.json_encode($toGPT).
						'\n\nInstructions: Based on the Prompt generate a proper response with help of Web search results(if the Web search results in the same context). Only if the prompt require links: (make curated list of links and descriptions using only the <a target="_blank">, write links with using <a target="_blank"> with mrgin Top of <a> tag is 5px and start order as number and write link first and then write description). Must not write links if its not necessary. Must not mention anything about the prompt text.';
					$history[] = ['role' => 'user', 'content' => $final_prompt];
				}
			}
			else{
				$history[] = ['role' => 'user', 'content' => $prompt];
			}
		}
        return response()->stream(function () use ($history, $opclient, $chat_bot,$prompt, $type, $openaiApiKey) {
			if ($type == 'chat') {
				$stream = $opclient->chat()->createStreamed([
					'model' => $chat_bot,
					'messages' => $history,
				]);
				$pid = pcntl_fork();
				if ($pid == -1) {
					// Fork failed
					die('Fork failed');
				} elseif ($pid) {
					// Parent process
					pcntl_wait($status); // Wait for child process to finish
				} else {
					// Child process
					foreach ($stream as $response) {
						$text = $response->choices[0]->delta->content;
						if (connection_aborted()) {
							break;
						}
						
						echo "event: data\n";
						echo 'data: '.json_encode(['message' => $text]);
						// echo "event: update\n";
						// echo 'data: ' . $text;
						echo "\n\n";
						ob_flush();
						flush();
					}
		
					echo "event: stop\n";
					echo "data: stopped";
					echo "\n\n";
					ob_flush();
					flush();
					exit(); // Exit child process
				}
            }
			elseif ($type == 'vision') {
				$images = json_decode($request->get('images'), true);
				$history[] = 
				[	
					'role' => 'user',
					'content' => array_merge(
						[
							[
								'type' => 'text',
								'text' => $prompt,
							],
						],
						collect($images)->map(function ($item) {
							if (Str::startsWith($item, 'http')) {
								$imageData = file_get_contents($item);
							} else {
								$imageData = file_get_contents(substr($item, 1, strlen($item) - 1));
							}
							$base64Image = base64_encode($imageData);

							return [
								'type' => 'image_url',
								'image_url' => [
									'url' => 'data:image/png;base64,'.$base64Image,
								],
							];
						})->toArray()
					),
				];

				$gclient = new Client();
				$url = 'https://api.openai.com/v1/chat/completions';
				$response = $gclient->post(
					$url,
					[
						'headers' => [
							'Authorization' => 'Bearer '.$openaiApiKey,
						],
						'json' => [
							'model' => 'gpt-4-vision-preview',
							'messages' => $history,
							'max_tokens' => 2000,
							'stream' => true,
						],
					],
				);
				foreach (explode("\n", $response->getBody()->getContents()) as $chunk) {
					if (strlen($chunk) > 5 && $chunk != 'data: [DONE]' && isset(json_decode(substr($chunk, 6, strlen($chunk) - 6))->choices[0]->delta->content)) {
						echo "event: data\n";
						echo 'data: '.json_encode(['message' => json_decode(substr($chunk, 6, strlen($chunk) - 6))->choices[0]->delta->content]);
						echo "\n\n";
						ob_flush();
						flush();
					}
				}
				echo "event: stop\n";
				echo "data: stopped\n\n";
				echo "\n\n";
				ob_flush();
				flush();
			}
        }, 200, [
            'Cache-Control' => 'no-cache',
            'X-Accel-Buffering' => 'no',
            'Content-Type' => 'text/event-stream',
        ]);
    }

	public function chatStream(Request $request)
    {
		$openaiKey = $this->settings->user_api_option ? explode(',', auth()->user()->api_keys) : explode(',', $this->settings->openai_api_secret);
		$openaiApiKey = $openaiKey[array_rand($openaiKey)];
		$client = OpenAI::factory()
		->withApiKey($openaiApiKey)
		->withHttpClient(new \GuzzleHttp\Client())
		->make();
		header('Content-type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        ob_end_flush();

		$type = $request->get('type');
		$chat_id = $request->get('chat_id');
		$prompt = $request->get('message');
        $realtime = $request->get('realtime');
        $categoryId = $request->get('category');
		$user = Auth::user();
		$userId = $user->id;
		$history = [];

		$chat_bot = $this->settings?->openai_default_model;
		$chat_bot == null ? 'gpt-3.5-turbo': $chat_bot;
		$chat_bot == 'gpt-4-vision-preview' ? $chat_bot = 'gpt-4-1106-preview' : $chat_bot;
        $realtimePrompt = $prompt;

        $chat = UserOpenaiChat::whereId($chat_id)->first();
		$category = $chat->category;
		if ($category->chat_completions == null) {
            $category->chat_completions = '[]';
        }
		if($type == 'vision'){
			$history[] = [
				'role' => 'system',
				'content' => "You will now play a character and respond as that character (You will never break character). Your name is Vision AI. Must not introduce by yourself as well as greetings. Help also with asked questions based on previous responses and images if exists and related."
			];
		}
		elseif ($category->chat_completions) {
			$chat_completions = json_decode($category->chat_completions, true);
			foreach ($chat_completions as $item) {
				$history[] = [
					'role' => $item['role'],
					'content' => $item['content'],
				];
			}
		} else {
			$history[] = ['role' => 'system', 'content' => 'You are a helpful assistant.'];
		}

		$lastThreeMessageQuery = $chat->messages()
			->whereNotNull('input')
			->orderBy('created_at', 'desc')
			->take(4)
			->get()
			->reverse();

		$vectorService = new VectorService();
		$extra_prompt = $vectorService->getMostSimilarText($prompt, $chat_id, 5 , $chat->chatbot_id);
		$count = count($lastThreeMessageQuery);
		if ($count > 1) {
			if($realtime && $this->settings_two->serper_api_key != null){	
				if ($extra_prompt != '') {
					$history[] = ['role' => 'user', 'content' => "'this file' means file content. Must not reference previous chats if user asking about pdf. Must reference file content if only user is asking about file content. Else just response as an assistant shortly and professionaly without must not referencing file content. . User: $prompt \n\n\n\n\n Document Content: \n $extra_prompt"];
				} else {
					if($type == 'vision') {
						foreach ($lastThreeMessageQuery as $threeMessage) {
							$history[] = [
								'role' => 'user', 
								'content' => array_merge(
									[
										[
											'type' => 'text',
											'text' => $threeMessage->input,
										],
									],
									collect($threeMessage->images)->map(function ($item) {
										if($item !== "undefined" || $item !== null) {
											if (Str::startsWith($item, 'http')) {
												$imageData = file_get_contents($item);
											} else {
												$imageData = file_get_contents(substr($item, 1, strlen($item) - 1));
											}
											$base64Image = base64_encode($imageData);

											return [
												'type' => 'image_url',
												'image_url' => [
													'url' => 'data:image/png;base64,'.$base64Image,
												],
											];
										}
									})->toArray()
								),
							];
							if ($threeMessage->response != null) {
								$history[] = ['role' => 'assistant', 'content' => $threeMessage->response];
							}
						}
					}else{
						foreach ($lastThreeMessageQuery as $threeMessage) {
							$history[] = ['role' => 'user', 'content' => $threeMessage->input];
							if ($threeMessage->output != null) {
								$history[] = ['role' => 'assistant', 'content' => $threeMessage->output];
							}else{
								$history[] = ['role' => 'assistant', 'content' => ''];
							}
						}
						$history[] = ['role' => 'user', 'content' => $prompt];
					}
					$sclient = new Client();
					$headers = [
						'X-API-KEY' => $this->settings_two->serper_api_key,
						'Content-Type' => 'application/json',
					];
					$body = [
						'q' => $realtimePrompt,
					];
					$response = $sclient->post('https://google.serper.dev/search', [
						'headers' => $headers,
						'json' => $body,
					]);
					$toGPT = $response->getBody()->getContents();
					try {
						$toGPT = json_decode($toGPT);
					} catch (\Throwable $th) {
					}

					$final_prompt =
						'Prompt: '.$realtimePrompt.
						'\n\nWeb search json results: '
						.json_encode($toGPT).
						'\n\nInstructions: Based on the Prompt generate a proper response with help of Web search results(if the Web search results in the same context). Only if the prompt require links: (make curated list of links and descriptions using only the <a target="_blank">, write links with using <a target="_blank"> with mrgin Top of <a> tag is 5px and start order as number and write link first and then write description). Must not write links if its not necessary. Must not mention anything about the prompt text.';
					$history[] = ['role' => 'user', 'content' => $final_prompt];
				}
			}else{
				if ($extra_prompt != '') {
					$lastThreeMessageQuery[$count - 1]->input = "'this file' means file content. Must not reference previous chats if user asking about pdf. Must reference file content if only user is asking about file content. Else just response as an assistant shortly and professionaly without must not referencing file content. \n\n\n\n\nUser qusetion: $prompt \n\n\n\n\n Document Content: \n $extra_prompt";
				}
				if($type == 'vision') {
					foreach ($lastThreeMessageQuery as $threeMessage) {
						$history[] = [
							'role' => 'user', 
							'content' => array_merge(
								[
									[
										'type' => 'text',
										'text' => $threeMessage->input,
									],
								],
								collect($threeMessage->images)->map(function ($item) {
									if($item !== "undefined" || $item !== null) {
										if (Str::startsWith($item, 'http')) {
											$imageData = file_get_contents($item);
										} else {
											$imageData = file_get_contents(substr($item, 1, strlen($item) - 1));
										}
										$base64Image = base64_encode($imageData);

										return [
											'type' => 'image_url',
											'image_url' => [
												'url' => 'data:image/png;base64,'.$base64Image,
											],
										];
									}
								})->toArray()
							),
						];
						if ($threeMessage->response != null) {
							$history[] = ['role' => 'assistant', 'content' => $threeMessage->response];
						}
					}
				}else{
					foreach ($lastThreeMessageQuery as $threeMessage) {
						$history[] = ['role' => 'user', 'content' => $threeMessage->input];
						if ($threeMessage->output != null) {
							$history[] = ['role' => 'assistant', 'content' => $threeMessage->output];
						}else{
							$history[] = ['role' => 'assistant', 'content' => ''];
						}
					}
					$history[] = ['role' => 'user', 'content' => $prompt];
				}
			}
		}else{
			if($realtime && $this->settings_two->serper_api_key != null){
				if ($extra_prompt != '') {
					$history[] = ['role' => 'user', 'content' => "'this file' means file content. Must not reference previous chats if user asking about pdf. Must reference file content if only user is asking about file content. Else just response as an assistant shortly and professionaly without must not referencing file content. . User: $prompt \n\n\n\n\n Document Content: \n $extra_prompt"];
				} else {
					$client = new Client();
					$headers = [
						'X-API-KEY' => $this->settings_two->serper_api_key,
						'Content-Type' => 'application/json',
					];
					$body = [
						'q' => $realtimePrompt,
					];
					$response = $client->post('https://google.serper.dev/search', [
						'headers' => $headers,
						'json' => $body,
					]);
					$toGPT = $response->getBody()->getContents();
					try {
						$toGPT = json_decode($toGPT);
					} catch (\Throwable $th) {
					}

					$final_prompt =
						'Prompt: '.$realtimePrompt.
						'\n\nWeb search json results: '
						.json_encode($toGPT).
						'\n\nInstructions: Based on the Prompt generate a proper response with help of Web search results(if the Web search results in the same context). Only if the prompt require links: (make curated list of links and descriptions using only the <a target="_blank">, write links with using <a target="_blank"> with mrgin Top of <a> tag is 5px and start order as number and write link first and then write description). Must not write links if its not necessary. Must not mention anything about the prompt text.';
					$history[] = ['role' => 'user', 'content' => $final_prompt];
				}
			}
			else{
				$history[] = ['role' => 'user', 'content' => $prompt];
			}
		}
		if ($type == 'chat') {
			Log::error($history);
			$result = $client->chat()->createStreamed([
				'model' => $chat_bot,
				'messages' => $history,
			]);

			$pid = pcntl_fork();
			if ($pid == -1) {
				// Fork failed
				die('Fork failed');
			} elseif ($pid) {
				// Parent process
				pcntl_wait($status); // Wait for child process to finish
			} else {
				// Child process
				foreach ($result as $response) {
					echo "event: data\n";
					echo 'data: '.json_encode(['message' => $response->choices[0]->delta->content])."\n\n";
					flush();
				}
				echo "event: stop\n";
				echo "data: stopped\n\n";
				exit(); // Exit child process
			}
		}
		elseif ($type == 'vision') {
			$images = json_decode($request->get('images'), true);
			$history[] = 
			[	
				'role' => 'user',
				'content' => array_merge(
					[
						[
							'type' => 'text',
							'text' => $prompt,
						],
					],
					collect($images)->map(function ($item) {
						if (Str::startsWith($item, 'http')) {
							$imageData = file_get_contents($item);
						} else {
							$imageData = file_get_contents(substr($item, 1, strlen($item) - 1));
						}
						$base64Image = base64_encode($imageData);

						return [
							'type' => 'image_url',
							'image_url' => [
								'url' => 'data:image/png;base64,'.$base64Image,
							],
						];
					})->toArray()
				),
			];

			$gclient = new Client();
			$url = 'https://api.openai.com/v1/chat/completions';
			$response = $gclient->post(
				$url,
				[
					'headers' => [
						'Authorization' => 'Bearer '.$openaiApiKey,
					],
					'json' => [
						'model' => 'gpt-4-vision-preview',
						'messages' => $history,
						'max_tokens' => 2000,
						'stream' => true,
					],
				],
			);
			foreach (explode("\n", $response->getBody()->getContents()) as $chunk) {
				if (strlen($chunk) > 5 && $chunk != 'data: [DONE]' && isset(json_decode(substr($chunk, 6, strlen($chunk) - 6))->choices[0]->delta->content)) {
					echo "event: data\n";
					echo 'data: '.json_encode(['message' => json_decode(substr($chunk, 6, strlen($chunk) - 6))->choices[0]->delta->content])."\n\n";
					flush();
				}
			}
			echo "event: stop\n";
			echo "data: stopped\n\n";
		}
    }


    public function lazyLoadImage(Request $request)
    {
        $items_per_page = 5;
        $offset = $request->get('offset', 0);
        $post_type = $request->get('post_type');
        $post = OpenAIGenerator::where('slug', $post_type)->first();

		$all_images = UserOpenai::where('user_id', Auth::id())
            ->where('openai_id', $post->id);

		$all_images_count = $all_images->count();
		$current_images_list = $all_images->orderBy('created_at', 'desc')
			->skip($offset)
			->take($items_per_page)
			->get();

        return response()->json([
            'images' => $current_images_list,
			'count_current' => $current_images_list->count() + $offset,
			'count_remaining' => $all_images_count - ($current_images_list->count() + $offset),
			'count_all' => $all_images_count,
        ]);
    }

    public function updateWriting(Request $request)
    {
        $user = $request->user();

        $content = $request->get('content');
        $prompt = $request->prompt;

        if ($content == null || $content == '') {
            return response()->json(['result' => '']);
        }

        if ($user->remaining_words <= 0 && $user->remaining_words != -1) {
            return response()->json(['errors' => 'You have no remaining words. Please upgrade your plan.'], 400);
        }

        if ($this->settings?->user_api_option) {
            $apiKeys = explode(',', auth()->user()?->api_keys);
        } else {
            $apiKeys = explode(',', $this->settings?->openai_api_secret);
        }

        $openaiKey = $apiKeys[array_rand($apiKeys)];

        $client = OpenAI::factory()
            ->withApiKey($openaiKey)
            ->withHttpClient(new \GuzzleHttp\Client())
            ->make();

        $completion = $client->chat()->create([
            'model' => 'gpt-3.5-turbo',
            'messages' => [[
                'role' => 'user',
                'content' => "$prompt\n\n$content",
            ]],
        ]);

        $content = $completion->choices[0]->message->content;

        userCreditDecreaseForWord($user, countWords($content));

        return response()->json(['result' => $completion->choices[0]->message->content]);
    }

    public function reWrite()
    {
        $openai = OpenAIGenerator::whereSlug('ai_rewriter')->firstOrFail();
        $settings = Setting::first();
        // Fetch the Site Settings object with openai_api_secret
        if ($this->settings?->user_api_option) {
            $apiKeys = explode(',', auth()->user()?->api_keys);
        } else {
            $apiKeys = explode(',', $this->settings?->openai_api_secret);
        }
        $apiKey = $apiKeys[array_rand($apiKeys)];

        $len = strlen($apiKey);
        $parts[] = substr($apiKey, 0, $l[] = rand(1, $len - 5));
        $parts[] = substr($apiKey, $l[0], $l[] = rand(1, $len - $l[0] - 3));
        $parts[] = substr($apiKey, array_sum($l));
        $apikeyPart1 = base64_encode($parts[0]);
        $apikeyPart2 = base64_encode($parts[1]);
        $apikeyPart3 = base64_encode($parts[2]);
        $apiUrl = base64_encode('https://api.openai.com/v1/chat/completions');

        return view('panel.user.openai.rewriter.index', compact(
            'apikeyPart1',
            'apikeyPart2',
            'apikeyPart3',
            'apiUrl',
        ));
    }
}