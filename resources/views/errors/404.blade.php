<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>RITE Slackbot 001</title>

		    <link rel="stylesheet" href="/css/tachyons.min.css">
        <!-- Global Site Tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=UA-81736260-3"></script>
        <script>
          window.dataLayer = window.dataLayer || [];
          function gtag(){dataLayer.push(arguments)};
          gtag('js', new Date());

          gtag('config', 'UA-81736260-3');
        </script>
    </head>
    <body class="sans-serif">
		<div class="vh-100 dt w-100 generatedBG generatedFG">
      <div class="pt6-ns pt4 mh6-ns mh3 cf">
        <div class="pb2 bb bw1 mb4">
          <span class="f1 f-headline-l b dib-ns db pr4 mb3">RS</span>
          <span class="f6 f4-ns dib-ns db pr4 lh-copy">RITE Slackbot 001</span>
        </div>
        <p class="lh-copy measure mb5">
        Sorry, that method is not defined.
        </p>
        <a href="http://staff.buffalostate.edu/paranacj" class="lh-copy link db b black underline-hover">Made by Chris Parana</a>
        <a href="mailto:stergip@buffalostate.edu" class="lh-copy link db b black underline-hover">Concept by Pete Stergion</a>
        <div class="tl tr-ns">
          <code class="f6 dib nowrap ba br2 ph2 pv2 mv5 generatedBGi generatedFGi">Build: {{ \Tremby\LaravelGitVersion\GitVersionHelper::getVersion() }}</code>
        </div>
        <p class="small mb5">
        This software is released to SUNY Buffalo State by <a href="http://parana.io" class="black b no-underline underline-hover">Parana Elektromotoren&reg;</a> under the terms of the <a href="license" class="black b no-underline underline-hover">MIT license</a>.
        </p>
      </div>
		</div>
    </body>
</html>
