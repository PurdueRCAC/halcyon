{!! '<'.'?'.'xml version="1.0" encoding="UTF-8" ?>' !!}
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/" xmlns:media="http://search.yahoo.com/mrss/">
	<channel>
		<title>{{ $meta['title'] }}</title>
		<link>{{ $meta['url'] }}</link>
		<description><![CDATA[{{ $meta['description'] }}]]></description>
		<atom:link href="{{ $meta['url'] }}" rel="self" type="application/rss+xml" />
		<language>{{ $meta['language'] }}</language>
		<lastBuildDate>{{ $meta['lastBuildDate'] }}</lastBuildDate>
		@foreach($items as $post)
			<item>
				<title><![CDATA[{!! $post->headline !!}]]></title>
				<link>{{ route('site.news.show', ['id' => $post->id]) }}</link>
				<guid isPermaLink="true">{{ route('site.news.show', ['id' => $post->id]) }}</guid>
				<description><![CDATA[{!! $post->formattedBody !!}]]></description>
				<pubDate>{{ $post->datetimenews->format(DateTime::RSS) }}</pubDate>
				@if ($post->type)
					<category>{{ $post->type->name }}</category>
				@endif
			</item>
		@endforeach
	</channel>
</rss>