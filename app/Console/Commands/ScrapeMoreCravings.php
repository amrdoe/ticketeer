<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Panther\Client;

class ScrapeMoreCravings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:scrape-more-cravings
                            {urls?* : The URLs to start with}
                            {--R|recursive : If it should scan pages linked in the scanned ones}
                            {--csv= : The CSV file to save the output, auto generated if omitted}
                            {--no-csv : Don\'t output CSV file }
                            {--city= : The city to find similar websites, otherwise "Near me" is used}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape venue details from MoreCravings';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $slugs = $this->argument('urls') ? collect($this->argument('urls'))->map(fn ($url) => $this->getSlug($url)) : collect(['brasserie-freedom-restaurant-and-bar']);

        $client = Client::createChromeClient(null, [
            '--headless=new',
            '--disable-gpu',
            '--no-sandbox',
            '--disable-dev-shm-usage',
            '--remote-debugging-port=9222',
            '--window-size=1200,1200',
        ], );

        if (! $this->option('no-csv')) {
            $defaultFileName = 'scraped_venues_'.date('Y-m-d_H-i').'.csv';
            $fileName = $this->option('csv') ?? $defaultFileName;
            $filePath = getcwd().'/'.$fileName;

            $file = fopen($filePath, 'w');

            $header = [
                'Title',
                'Address',
                'Images',
                'About',
                'Pricing',
                'Chef',
                'Cutlery',
                'Dress Code',
                'Latitude',
                'Longitude',
                'Phone',
                'Email',
                'Website',
            ];

            fputcsv($file, $header);

            $this->info("The data is being saved to: $fileName");
        }

        for ($i = 0; $i < $slugs->count(); $i++) {
            $slug = $slugs[$i];

            try {
                $this->info("Starting scraper for: $slug");

                $this->line('Requesting page...');
                $crawler = $client->request('GET', "https://www.morecravings.com/en/venues/$slug");

                $client->waitFor('#search_by_location');
                $city = $this->option('city');

                if ($city && $crawler->filter('#search_by_location')->attr('value') !== $city) {
                    $this->info('Changing city to: '.$city);
                    $client->executeScript(<<<'JS'
                        const input = document.getElementById('search_by_location');
                        if (!input) return;

                        input.focus();

                        const nativeInputValueSetter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                        nativeInputValueSetter.call(input, arguments[0]);

                        input.dispatchEvent(new Event('input', { bubbles: true }));
                        input.dispatchEvent(new Event('change', { bubbles: true }));
                    JS, [$city]);

                    $client->waitFor("//span[@role='button' and normalize-space()='$city']");

                    $client->executeScript(<<<'JS'
                        document.evaluate(`//span[@role='button' and normalize-space()='${arguments[0]}']`, document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue?.click();
                    JS, [$city]);

                    $i--;

                    continue;
                }

                $client->waitFor('h1 + span');

                $title = $crawler->filter('h1')->text();
                $this->info("Title: $title");

                $address = $crawler->filter('h1 + span')->text();
                $this->line("Address: $address");

                $client->executeScript(<<<'JS'
                    document.querySelector('a:has(> .icon-photo)')?.click();
                JS);
                $client->waitFor('.lg-react-element > a');

                $images = $crawler->filter('.lg-react-element > a')->each(fn ($node) => $node->attr('href'));
                if (count($images)) {
                    $this->table(['Images'], array_map(fn ($i) => [$i], $images));
                }

                $client->executeScript(<<<'JS'
                    document.querySelector('.icon-arrow-right')?.click();
                JS);

                $client->waitFor('[id$="tabpane-about"]');

                $aboutDiv = $crawler->filter('[id$="tabpane-about"] > section:nth-of-type(1) > div:nth-of-type(1)');
                $about = $aboutDiv->count() > 0 ? $aboutDiv->text() : '';
                if ($about) {
                    $this->line("About: $about");
                }

                $tags = $crawler->filter('[id$=tabpane-about] > section:nth-of-type(1) > div:nth-of-type(2)');
                $pricing = $tags->children()->count() > 0 ? $tags->children()->text() : '';
                if ($pricing) {
                    $this->line("Pricing: $pricing");
                }

                $chefTag = $tags->filter('div:has(.icon-chef)');
                $chef = $chefTag->count() > 0 ? $chefTag->text() : '';
                if ($chef) {
                    $this->line("Chef: $chef");
                }

                $cutleryTag = $tags->filter('div:has(.icon-cutlery)');
                $cutlery = $cutleryTag->count() > 0 ? $cutleryTag->text() : '';
                if ($cutlery) {
                    $this->line("Cutlery: $cutlery");
                }

                $dressCodeTag = $tags->filter('div:has(.icon-shirt)');
                $dressCode = $dressCodeTag->count() > 0 ? $dressCodeTag->text() : '';
                if ($dressCode) {
                    $this->line("Dress Code: $dressCode");
                }

                $directionsButton = $crawler->selectLink('Directions');
                [$latitude, $longitude] = $directionsButton->count() > 0 ? explode(',', substr($directionsButton->attr('href'), 48)) : ['', ''];
                if ($latitude) {
                    $this->line("Latitude: $latitude");
                }
                if ($longitude) {
                    $this->line("Longitude: $longitude");
                }

                $phoneButton = $crawler->filter('a:has(.icon-call-1)');
                $phone = $phoneButton->count() > 0 ? substr($phoneButton->attr('href'), 4) : '';
                if ($phone) {
                    $this->line("Phone: $phone");
                }

                $emailButton = $crawler->selectLink('Email');
                $email = $emailButton->count() > 0 ? substr($emailButton->attr('href'), 7) : '';
                if ($email) {
                    $this->line("Email: $email");
                }

                $websiteButton = $crawler->selectLink('Website');
                $website = $websiteButton->count() > 0 ? $websiteButton->attr('href') : '';
                if ($website) {
                    $this->line("Website: $website");
                }

                if (isset($file)) {
                    $row = [
                        $title,
                        $address,
                        implode("\n", $images),
                        $about,
                        $pricing,
                        $chef,
                        $cutlery,
                        $dressCode,
                        $latitude,
                        $longitude,
                        $phone,
                        $email,
                        $website,
                    ];

                    fputcsv($file, $row);
                }

                if (! $this->option('recursive')) {
                    continue;
                }

                $client->waitFor('.venueTile');

                $crawler->filter('.venueTile a.stretched-link')->each(function ($a) use ($slugs) {
                    $slug = $this->getSlug($a->attr('href'));
                    if ($slugs->contains($slug)) {
                        return;
                    }
                    $slugs->push($slug);
                    $this->info("Queued: $slug");
                });

            } catch (\Exception $e) {
                $this->error('Error: '.$e->getMessage());
            }
        }

        $client->close();
        if (isset($file)) {
            fclose($file);
        }
    }

    private function getSlug(string $url): string
    {
        return last(explode('/', head(explode('?', head(explode('#', $url))))));
    }
}
