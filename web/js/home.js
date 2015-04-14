/**
 * Handles Asynchronos Loading and rendering of tweets
 * 
 * @author Mukesh Sharma <cogentmukesh@gmail.com>
 */
(function() {
    //  Maximum number of panels to show in a Bootstrap Row 
    var TWEET_PANELS_IN_ROW = 2;

    //  maxId to get the tweets less then in subsequent call
    //  lowest tweetId on page
    var maxId;

    //  Count of all the Tweets panel rendered on page
    var numberOfTweetsPanel = 0;

    //  Template of the tweet panel that will be appended 
    var tweetPanelTemplate = $('#tweet-template').html();
    
    /**
     * Render the Tweet using tweetTemplate and the fetched tweet Data
     *
     * @param {array} Tweets to render
     */
    function renderTweets(tweets) {
        $.each(tweets, function(index, tweet) {
            
            // Create a new Tweet Panel
            var tweetPanel = $(tweetPanelTemplate);
            
            // Add a new Bootstrap row after every max tweetPanelInRow
            if (numberOfTweetsPanel %  TWEET_PANELS_IN_ROW == 0) {
                $(".container").append('<div class="row"/>'); 
            }

            tweetPanel.find('.tweet').html(tweet.text);
            tweetPanel.find('.retweet-count').html(tweet.retweet_count);
            tweetPanel.find('.tweet-owner-name').html(tweet.name);
            tweetPanel.find('.tweet-owner-handler').html('<small>@' + tweet.screen_name + '</small>');
            $(".row").last().append(tweetPanel);
            numberOfTweetsPanel++;
        });
    }

    /**
     * Fetch Tweets Asynchronosly from the Platfrom 
     */
    function fetchTweets() {
        $.getJSON('fetch', {'maxId': maxId}, function(data) {
            if (data.status !== true) {
                alert("Unable to fetch tweets. Please try again later");
                return false;
            }
            
            // Twitter maxId would be the lowest tweetId on page
            // so that in subsequent call we can fetch tweets less than maxId
            maxId = data.tweets[data.tweets.length - 1].id_str;
            // Render Tweets
            renderTweets(data.tweets)
        }) 
         .fail(function() {
            alert("Unable to fetch tweets. Please try again later");
        });
    }

    /**
     * On Dom ready, Try to fetch the Tweets from Platform Asynchronously
     */
    $(document).ready(function() {
        // Fetch and render tweets 
        fetchTweets();
    });

    /**
     * Fetch more tweets once we reach at bottom of the page
     */
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() == $(document).height()) {
            // Fetch and render tweets 
            fetchTweets();
        }
    });

}) ();
