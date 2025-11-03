{include file='header'}

<section class="section">
    <header class="sectionHeader">
        <h1>{lang}wcf.cs2bannliste.add{/lang}</h1>
    </header>

    {if $error}
        <div class="error">{$error}</div>
    {elseif $success}
        <div class="success">{$success}</div>
    {/if}

    <form method="post" class="jsForm">
        <dl class="formGroup">
            <dt><label for="player_name">{lang}wcf.cs2bannliste.name{/lang}</label></dt>
            <dd><input type="text" id="player_name" name="player_name" class="inputText" required></dd>
        </dl>

        <dl class="formGroup">
            <dt><label for="player_steamid">{lang}wcf.cs2bannliste.steamid{/lang}</label></dt>
            <dd><input type="text" id="player_steamid" name="player_steamid" class="inputText" required></dd>
        </dl>

        <dl class="formGroup">
            <dt><label for="reason">{lang}wcf.cs2bannliste.reason{/lang}</label></dt>
            <dd><input type="text" id="reason" name="reason" class="inputText" required></dd>
        </dl>

        <dl class="formGroup">
            <dt><label for="duration">{lang}wcf.cs2bannliste.duration{/lang}</label></dt>
            <dd><input type="text" id="duration" name="duration" class="inputText" placeholder="z.B. permanent oder 7d"></dd>
        </dl>

        <div class="formSubmit">
            <button type="submit" class="button buttonPrimary">{lang}wcf.cs2bannliste.save{/lang}</button>
            <a href="{link application='wcf' controller='Banlist'}{/link}" class="button">{lang}wcf.cs2bannliste.back{/lang}</a>
        </div>
    </form>
</section>

{include file='footer'}

