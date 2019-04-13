<tr>
    <td>
        <table class="footer" align="center" width="570" cellpadding="0" cellspacing="0">
            <tr>
                <td class="content-cell" align="center">
			        <small style="display: block; font-style: italic; color: #666;">E-posta bildirimlerine müdehale etmek için, "<a href="{{ route('settings.notifications') }}">E-posta Bildirimleri</a>" sayfasından ilgili ayarları güncelleyebilirsiniz.</small>
                    <br />
                    {{ Illuminate\Mail\Markdown::parse($slot) }}
                    <br />
			        <a href="https://veri.zone">
			            <img width="113" height="32" alt="veri.zone" src="{{ secure_asset('img/olive_logo-sm-grey.png') }}" />
			        </a>
			    </td>
			</tr>
        </table>
    </td>
</tr>
