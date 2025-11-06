{include file='header'}

<section class="section">
    <header class="sectionHeader">
        <h1>{lang}wcf.cs2bannliste.title{/lang}</h1>

        {if $canAdd}
            <p>
                <a href="{link application='wcf' controller='BanAdd'}{/link}" class="button buttonPrimary">
                    {lang}wcf.cs2bannliste.add{/lang}
                </a>
            </p>
        {/if}
    </header>

    {if $error}
        <p style="color:#c00; font-weight:bold;">{$error}</p>
    {elseif !$bans|count}
        <p>{lang}wcf.cs2bannliste.empty{/lang}</p>
    {else}
        <div class="section">
            <table class="table">
                <thead>
                    <tr>
                        <th>{lang}wcf.cs2bannliste.name{/lang}</th>
                        <th>{lang}wcf.cs2bannliste.steamid{/lang}</th>
                        <th>{lang}wcf.cs2bannliste.reason{/lang}</th>
                        <th>{lang}wcf.cs2bannliste.admin{/lang}</th>
                        <th>{lang}wcf.cs2bannliste.created{/lang}</th>
                        <th>{lang}wcf.cs2bannliste.duration{/lang}</th>
                        <th>{lang}wcf.cs2bannliste.status{/lang}</th>
                        <th>{lang}wcf.cs2bannliste.actions{/lang}</th>
                    </tr>
                </thead>

                <tbody>
                    {foreach from=$bans item=ban}
                        <tr>
                            <td>{$ban.player_name}</td>
                            <td><code>{$ban.player_steamid}</code></td>
                            <td>{$ban.reason}</td>
                            <td>{$ban.admin_name}</td>
                            <td>{$ban.created}</td>
                            <td>{$ban.duration}</td>

                            {if $ban.status == 'UNBANNED'}
                                <td style="color:#3a3;">{$ban.status}</td>
                            {elseif $ban.status == 'EXPIRED'}
                            <td style="color:#ff9900;">{$ban.status}</td>
                            {else}
                                <td style="color:#c00;">{$ban.status}</td>
                            {/if}

                            <td>
                                {if $canEdit}
                                    <a href="{link application='wcf' controller='BanEdit' id=$ban.id}{/link}" class="button small">
                                        {lang}wcf.cs2bannliste.edit{/lang}
                                    </a>
                                {/if}

                                {if $canUnban && $ban.status != 'UNBANNED'}
                                    <a href="{link application='wcf' controller='BanUnban' id=$ban.id}{/link}" class="button small">
                                        {lang}wcf.cs2bannliste.unban{/lang}
                                    </a>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
    {/if}
</section>

{include file='footer'}

