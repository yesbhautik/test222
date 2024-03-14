insert into `openai` (`title`, `description`, `slug`, `active`, `questions`, `image`, `premium`, `type`, `prompt`,
                      `custom_template`, `tone_of_voice`, `color`, `filters`, `updated_at`, `created_at`)
values (
        'AI RSS', 'Generate unique content with RSS Feed.', 'ai_rss', '1',
        '[{"name":"rss_feed","type":"rss_feed","question":"URL","select":""},{"name":"title","type":"select","question":"Fetched Post Title","select":"<option value=\"\">Enter the Feed URL, please!</option>"}]',
        '<svg xmlns="http://www.w3.org/2000/svg" width="44" height="44" viewBox="0 0 24 24" stroke-width="1.5" stroke="#2c3e50" fill="none" stroke-linecap="round" stroke-linejoin="round"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M5 19m-1 0a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" /><path d="M4 4a16 16 0 0 1 16 16" /><path d="M4 11a9 9 0 0 1 9 9" /></svg>',
        '0', 'rss', '', '0', '0', '#FF9E4D', 'rss', '2024-03-01 12:03:05', '2024-03-01 12:03:05'
       )