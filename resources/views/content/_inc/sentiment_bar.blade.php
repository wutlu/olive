<div class="card-sentiment d-flex justify-content-between">
    <div style="width: {{ $pos*100 }}%;" class="sentiment-item light-green-text accent-4 d-flex">
        @if ($pos > 0.2)
	        <i class="material-icons light-green-text align-self-center">sentiment_very_satisfied</i>
	        <span class="badge light-green-text align-self-center">{{ $pos*100 }}%</span>
        @endif
    </div>
    <div style="width: {{ $neu*100 }}%;" class="sentiment-item grey-text d-flex">
        @if ($neu > 0.2)
	        <i class="material-icons grey-text text-darken-2 align-self-center">sentiment_neutral</i>
	        <span class="badge grey-text text-darken-2 align-self-center">{{ $neu*100 }}%</span>
        @endif
    </div>
    <div style="width: {{ $neg*100 }}%;" class="sentiment-item red-text accent-4 d-flex">
        @if ($neg > 0.2)
	        <i class="material-icons red-text align-self-center">sentiment_very_dissatisfied</i>
	        <span class="badge red-text align-self-center">{{ $neg*100 }}%</span>
        @endif
    </div>
</div>
<div class="card-action d-flex justify-content-between">
    <span class="left-align">
        <small class="d-block grey-text">OLUŞTURULDU</small>
        <time>{{ date('d.m.Y H:i', strtotime($document['_source']['created_at'])) }}</time>
    </span>
     <span class="right-align">
        <small class="d-block grey-text">ALINDI</small>
        <time>{{ date('d.m.Y H:i', strtotime($document['_source']['called_at'])) }}</time>
    </span>
</div>
