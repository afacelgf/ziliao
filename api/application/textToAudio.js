function textToAudio() {
// (function () {

    return '111';
    "use strict";
    console.log('8888');
    var sdk = require("microsoft-cognitiveservices-speech-sdk");
    // var sdk = require("microsoft.cognitiveservices.speech.sdk");

    var audioFile = "test05.wav";
    // "SPEECH_KEY" and "SPEECH_REGION"
    const speechConfig = sdk.SpeechConfig.fromSubscription("9a6d14c32ebb4061b03fa0524cc0108d", 'eastasia');
    //文件输出
    const audioConfig = sdk.AudioConfig.fromAudioFileOutput(audioFile);

    // var speechSynthesisVoiceName  = "en-US-JennyNeural";
    var speechSynthesisVoiceName = "zh-CN-YunxiNeural";//配音名称

    // break中断

    // mstts:silence 用于在两个句子之间添加 200 毫秒的静音
    //     <mstts:silence  type="Sentenceboundary" value="200ms"/>
    // If we're home schooling, the best we can do is roll with what each day brings and try to have fun along the way.
    // A good place to start is by trying out the slew of educational apps that are helping children stay happy and smash their schooling at the same time.
    // </voice>
    //name:用于文本转语音输出的语音
    var ssml = `<speak version='1.0' xml:lang='en-US' xmlns='http://www.w3.org/2001/10/synthesis' xmlns:mstts='http://www.w3.org/2001/mstts'> 
   
    
    <voice name="en-US-JennyMultilingualNeural">
    他是个好人.
    </voice>
    <voice name="zh-CN-XiaoxiaoNeural">
    他是个好人.
    </voice>
    <voice name="zh-CN-YunxiNeural">
    他是个好人.
    </voice>
    <voice name="zh-CN-XiaoyouNeural">
    他是个好人.
    </voice>
    <voice name="zh-CN-YunjianNeural">
    他是个好人.
    </voice>
    <voice name="zh-CN-YunyeNeural">
    他是个好人.
    </voice>
    <voice name="zh-CN-henan-YundengNeural">
    他是个好人.
    </voice>
    <voice name="zh-CN-liaoning-XiaobeiNeural">
    他是个好人.
    </voice>
    <voice name="zh-CN-shandong-YunxiangNeural">
    他是个好人.
    </voice>
      
    </speak>`;
    //     <voice name="en-US-JennyNeural" effect="eq_car">
    //     This is the <break /> text that is spoken.Welcome <break strength="medium" /> to text to speech.
    //     Welcome <break time="750ms" /> to text to speech.
    // </voice>

    // 指定是否在Word边界事件中请求句子边界。
    speechConfig.setProperty(sdk.PropertyId.SpeechServiceResponse_RequestSentenceBoundary, "true");

    // Create the speech speechSynthesizer.
    var speechSynthesizer = new sdk.SpeechSynthesizer(speechConfig, audioConfig);

    speechSynthesizer.bookmarkReached = function (s, e) {
        var str = `BookmarkReached event: \
            \r\n\tAudioOffset: ${(e.audioOffset + 5000) / 10000}ms \
            \r\n\tText: \"${e.text}\".`;
        console.log(str);
    };

    speechSynthesizer.synthesisCanceled = function (s, e) {
        console.log("SynthesisCanceled event");
    };

    speechSynthesizer.synthesisCompleted = function (s, e) {
        var str = `SynthesisCompleted event: \
                    \r\n\tAudioData: ${e.result.audioData.byteLength} bytes \
                    \r\n\tAudioDuration: ${e.result.audioDuration}`;
        console.log(str);
    };

    speechSynthesizer.synthesisStarted = function (s, e) {
        console.log("SynthesisStarted event");
    };

    speechSynthesizer.synthesizing = function (s, e) {
        var str = `Synthesizing event: \
            \r\n\tAudioData: ${e.result.audioData.byteLength} bytes`;
        console.log(str);
    };

    speechSynthesizer.visemeReceived = function (s, e) {
        var str = `VisemeReceived event: \
            \r\n\tAudioOffset: ${(e.audioOffset + 5000) / 10000}ms \
            \r\n\tVisemeId: ${e.visemeId}`;
        console.log(str);
    };

    speechSynthesizer.wordBoundary = function (s, e) {
        // Word, Punctuation, or Sentence
        var str = `WordBoundary event: \
            \r\n\tBoundaryType: ${e.boundaryType} \
            \r\n\tAudioOffset: ${(e.audioOffset + 5000) / 10000}ms \
            \r\n\tDuration: ${e.duration} \
            \r\n\tText: \"${e.text}\" \
            \r\n\tTextOffset: ${e.textOffset} \
            \r\n\tWordLength: ${e.wordLength}`;
        console.log(str);
    };

    // Synthesize the SSML
    console.log(`SSML to synthesize: \r\n ${ssml}`)
    console.log(`Synthesize to: ${audioFile}`);
    speechSynthesizer.speakSsmlAsync(ssml,
        function (result) {
            if (result.reason === sdk.ResultReason.SynthesizingAudioCompleted) {
                console.log("SynthesizingAudioCompleted result");
            } else {
                console.error("Speech synthesis canceled, " + result.errorDetails +
                    "\nDid you set the speech resource key and region values?");
            }
            speechSynthesizer.close();
            speechSynthesizer = null;
        },
        function (err) {
            console.trace("err - " + err);
            speechSynthesizer.close();
            speechSynthesizer = null;
        });
// }());
};

function addab(a, b) { return a + b; };