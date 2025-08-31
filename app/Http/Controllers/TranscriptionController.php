<?php

namespace App\Http\Controllers;

use view;
use Illuminate\Http\Request;
use App\Services\AudioService;
use App\AiAgents\TranslationAgent;
use App\Http\Controllers\Controller;

class TranscriptionController extends Controller
{
    protected $audioService;

    public function __construct(AudioService $audioService)
    {
        $this->audioService = $audioService;
    }

    public function index()
    {
        return view('transcribe');
    }

    public function transcribe(Request $request)
    {
       set_time_limit(300); // sets execution time to 300 seconds (5 minutes)

        //dd($request->all());
        $request->validate([
            'audio' => 'required|file|mimes:mp3,wav,ogg,flac|max:51200',
            'option' => 'required',
        ]);

        if ($request->option == 'AWS') {
            $result = $this->audioService->transcribe($request->file('audio'));
        } else {
            $result = include resource_path('data/aws-hi.php');

            $localData = [];

            // Access directly inside "results.transcripts"
            if (isset($result['results']['transcripts'])) {
                $localData['transcripts'] = $result['results']['transcripts'][0]['transcript'] ?? [];
            }
            
            if ($result['results']['audio_segments']) {
                foreach ($result['results']['audio_segments'] as $segment) {
                    $localData['audio_segments'][] = [
                        'id' => $segment['id'],
                        'transcript' => $segment['transcript'],
                        'start_time' => $segment['start_time'],
                        'end_time' => $segment['end_time'],
                        'speaker_label' => $segment['speaker_label'],
                    ];
                }
            }
           // dd($localData);
        // $response = TranslationAgent::for('translation')->respond(json_encode($localData));

       $response = '{
    "status": "success",
    "source": "local",
    "data": {
        "customerSentiment": "negative",
        "guidelineAdherence": "average",
        "issueResolution": "unresolved",
        "communicationClarity": "average",
        "empathyLevel": "low",
        "strengths": [
            "attempted to provide technical explanation",
            "maintained politeness"
        ],
        "weaknesses": [
            "unclear communication",
            "failed to resolve issue",
            "lack of empathy"
        ],
        "suggestions": [
            "improve clarity in technical explanations",
            "show more empathy",
            "ensure issue resolution"
        ],
        "rating": 2.2,
        "overallSummary": "The agent struggled to communicate clearly and resolve the issue, leading to customer frustration."
    },
    "transcripts": "सिर मैं रोशन आपकी किस प्रकार सहायता कर सकता हूँ? आता ड्यूटी । हेलो में आपका स्वागत है मैं आपकी किस प्रकार सहायता कर सकता हूँ सिर मैंने एक पेमेंट मारा है कैश कलेक्शन से लेकिन वो पेंडिंग शो हो रहा है अभी तक । माफी चाहता हूँ जो भी दिक्कत हुई थी सिर निश्चिंत रहे इस विषय में पूरी सहायता की जाएगी । जिस नंबर से बात हो रही है सिर यही आप का हाँ यही नंबर है सिर आज की डेट में आपने जो किए थे तीन हज़ार दो सौ पचहत्तर हाँ तीन हज़ार दो सौ पचहत्तर ठीक है लाइन पे रहिए मैं डिटेल्स आप चेक कर लेता हूँ । आपने अभी किया । एक पंद्रह मिन्यूट पहले पंद्रह से बीस मिन्यूट पहले ठीक है देखिए अगर आप आवाज़ आ रही है आपको मेरी हाँ सिर कभी भी अगर आप कैश कलेक्शन का ट्रांजेक्शन करते हैं ना ये जनरली इंस्टेंट अपडेट हो जाती है । अगर इंस्टेंटली अपडेट नहीं होते है ना मैक्सिमम फोर्टी फाइव फोर्टी फाइव मिनिट्स में अपडेट हो जाती है सिर । अगर उसके भी अलावा अगर किसी कारणवश डीलर के कोई प्रॉब्लम होती है तो मैक्सिमम टाइमिंग होता है ट्वेंटी फॉर वर्किंग हॉर्स का बट देखिए इतना टाइम नहीं लगता है जनरली विथिन फोर्टी फाइव मिनिट्स में ही क्लियर हो जाती है ट्रांजेक्शन अगर सर्टेन केस में अगर फोर्टी फाइव मिनिट्स में भी क्लियर नहीं होती तो मैक्सिमम ट्वेंटी फॉर हॉर्स वर्किंग हॉर्स के अंदर ही बिलर के एंड में अपडेट हो ही जाती है । मैं डिटेल्स हमारी एंड से अपडेट करवा देता हूँ आगे हम अपने एंड से ट्राई करेंगे अगर जल्दी ठीक है आप कंप्लेंट कैसे होते हैं ना । नहीं हो सकती है क्योंकि ट्रांजेक्शन आलरेडी पेम के एंडिंग है मैं डिटेल्स आगे शेयर कर देता हूँ उसके लिए ठीक है सिर होने के लिए मैक्सिमम फोर्टी फाइव मिनिट्स के अंदर भी अपडेट हो सकती है ये ठीक है ठीक है ना ठीक है ना हाँ कुछ कैंसल कैंसिलेशन के चांस है क्या सिर । से सक्सेसफुल जा चूका है अभी बिलर के एंड से कुछ फ्लक्चुएशन होता है उधर से अगर कुछ प्रॉब्लम होती है तो वो अलग बात है के सक्सेसफुल जा चूका है सिर उसी के लिए अभी रिक्वेस्ट करना चाहूँगा अभी डबल पेमेंट आप मत कीजिए फिलहाल के लिए ठीक है क्योंकि इसका स्टेटस अपडेट हमारे पास भी नहीं है एक्चुअली क्या है सिर ठीक है उसी के लिए फोर्टी फाइव मिन्यूट से लेकर चौबीस घंटे का टाइम होता है । एक्जेक्टली स्टेटस कन्फर्मेशन के लिए ठीक है सिर, वैट कीजिए उससे पहले ही आपका अपडेट हो जाएगा ठीक है ठीक है इसके इसके अलावा मैं आपकी और कोई सहायता कर सकता हूँ सिर सिर बस यही पे में कॉल करने के लिए धन्यवाद सिर आपका दिन शुभ रहे । तुम बोली थी"
}';

$response = json_decode($response, true);
return $response;
            $result = [
                'status' => 'success',
                'source' => 'local',
                'data' => $response,
                'transcripts' => $localData['transcripts'],
            ];
        }
        return response()->json($result);
        }
}
