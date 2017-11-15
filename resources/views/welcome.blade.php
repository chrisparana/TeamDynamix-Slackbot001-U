<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Slackbot 001</title>

    		<link rel="stylesheet" href="/css/tachyons.min.css">
        <style>
          .generatedBG {
            background-color: {{ $colors[0] }}
          }
          .generatedFG {
            color: {{ $colors[1] }}
          }
          .generatedBGi {
            background-color: {{ $colors[1] }}
          }
          .generatedFGi {
            color: {{ $colors[0] }}
          }
        </style>
    </head>
    <body class="sans-serif">
		<div class="vh-100 dt w-100 generatedBG generatedFG">
      <div class="pt6-ns pt4 mh6-ns mh3 cf">
        <div class="pb2 mb4">
          <span class="f1 f-headline-l b dib-ns db pr4 mb3">TS</span>
          <span class="f6 f4-ns dib-ns db pr4 lh-copy">TeamDynamix Slackbot 001 (Command Version)</span>
        </div>
        <section class="mb5 ba bw1 br2 pa3 pa4-ns">
          <div class="bb bw1 mb4">
            <p class="f6 ttu tracked mt0 mr2 ba br2 ph2 pv1 dib generatedBGi generatedFGi">Info</p>
            <h1 class="f6 mt0 dib">Statistics</h1>
          </div>
          <article class="mv4" data-name="slab-stat">
            <dl class="dib mr5">
              <dd class="f6 f5-ns b ml0">Users</dd>
              <dd class="f3 f2-ns b ml0">{{ $stats[0] }}</dd>
            </dl>
            <dl class="dib mr5">
              <dd class="f6 f5-ns b ml0">Searches</dd>
              <dd class="f3 f2-ns b ml0">{{ $stats[1] }}</dd>
            </dl>
            <dl class="dib mr5">
              <dd class="f6 f5-ns b ml0">Look Ups</dd>
              <dd class="f3 f2-ns b ml0">{{ $stats[2] }}</dd>
            </dl>
            <dl class="dib mr5">
              <dd class="f6 f5-ns b ml0">Interactions</dd>
              <dd class="f3 f2-ns b ml0">{{ $stats[4] }}</dd>
            </dl>
          </article>
          <p class="measure f6 mt6-ns mt4">
            <code>
              CP_TD/S API and bot microservice v{{ $version }}
            </code>
          </p>
          <p class="measure f6">
            <code>
              Build: {{ \Tremby\LaravelGitVersion\GitVersionHelper::getVersion() }}
            </code>
          </p>
        </section>
        <a href="http://staff.buffalostate.edu/paranacj" class="lh-copy link db b generatedFG underline-hover">Made by Chris Parana</a>
        <a href="mailto:stergip@buffalostate.edu" class="lh-copy link db b generatedFG underline-hover">Concept by Pete Stergion</a>
        <p class="small mb5">
          <a href="license" class="generatedFG b no-underline underline-hover">MIT License</a>
        </p>
      </div>
		</div>
    </body>
</html>
