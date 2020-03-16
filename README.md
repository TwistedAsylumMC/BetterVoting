# BetterVoting
![](assets/logo.png)

BetterVoting is a PocketMine-MP plugin for letting players claim their rewards from voting for your server.

[![](https://poggit.pmmp.io/shield.state/BetterVoting)](https://poggit.pmmp.io/p/BetterVoting) [![](https://poggit.pmmp.io/shield.dl.total/BetterVoting)](https://poggit.pmmp.io/p/BetterVoting)

## What's new in 2.0?
 - All vote requests use a separate thread instead of async tasks, boosting performance
 - Complete rewrite & overall performance boost
 - '/vote info' has been added, it shows the server's last cached information
 - '/vote top' now uses locally cached data, making it faster to respond
 - All messages are now editable in the config
 - Automatic vote claiming, when a player joins or the cache is updated, online players with unclaimed votes will have their vote automatically claimed
 - PlayerVoteEvent has been added for plugin developers
> Note: Data is cached & reupdated every 3 minutes due to cache time on MinecraftPocket-Server's API