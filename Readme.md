# TYPO3 Extension "nxcachetags"

Simplifies cache handling in TYPO3. Provides mechanisms for nested cached fragments,
e.g. a news list. In a news list the single news can be cached and the whole list can
be cached again. If a news in the list changes, not only must the cache for the news
item be flushed, but also the whole list. As all other news items are still cached,
only the changed item needs to be rendered while all other items can still be fetched
from cache.

## Example

```html
<div>
	<ct:tagEnvironment objectOrCacheTag="{news}" />
	<f:for each="{news}" as="newsItem">
		<ct:cache identifiedBy="{0: newsItem}">
			<h3>{newsItem.title}</h3>
		</ct:cache>
	</f:for>
</div>
```
