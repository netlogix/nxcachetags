# TYPO3 Extension nxcachetags

[![stability-beta](https://img.shields.io/badge/stability-beta-33bbff.svg)](https://github.com/netlogix/nxcachetags)
[![TYPO3 V10](https://img.shields.io/badge/TYPO3-10-orange.svg)](https://get.typo3.org/version/10)
[![TYPO3 V11](https://img.shields.io/badge/TYPO3-11-orange.svg)](https://get.typo3.org/version/11)
[![Minimum PHP Version](https://img.shields.io/badge/php-%3E%3D%207.4-8892BF.svg)](https://php.net/)
[![GitHub CI status](https://github.com/netlogix/nxcachetags/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/netlogix/nxcachetags/actions)

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
