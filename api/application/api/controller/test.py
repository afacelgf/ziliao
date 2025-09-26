#!/usr/bin/env python
from azure.cognitiveservices.speech import AudioDataStream, SpeechConfig, SpeechSynthesizer, SpeechSynthesisOutputFormat
from azure.cognitiveservices.speech.audio import AudioOutputConfig
import azure.cognitiveservices.speech as speechsdk

speech_key, service_region = "9a6d14c32ebb4061b03fa0524cc0108d", "eastasia"
speech_config = speechsdk.SpeechConfig(subscription=speech_key, region=service_region)

speech_config.speech_synthesis_language = "zh-CN"
speech_config.speech_synthesis_voice_name ="zh-CN-XiaoyouNeural"

audio_config = AudioOutputConfig(filename="file.mp3")
synthesizer = SpeechSynthesizer(speech_config=speech_config, audio_config=audio_config)
# print("Type some text that you want to speak...")
# text = input()
text = "我是个好人"
# with open('text.txt', 'r',encoding='utf-8',errors='ignore') as f:
#     text = f.read()

result = synthesizer.speak_text_async(text).get()
if result.reason == speechsdk.ResultReason.SynthesizingAudioCompleted:
    stream = AudioDataStream(result)
    stream.save_to_wav_file("file.mp3")
    print("Speech synthesized to speaker for text [{}]".format(text))
elif result.reason == speechsdk.ResultReason.Canceled:
    cancellation_details = result.cancellation_details
    print("Speech synthesis canceled: {}".format(cancellation_details.reason))
    if cancellation_details.reason == speechsdk.CancellationReason.Error:
        if cancellation_details.error_details:
            print("Error details: {}".format(cancellation_details.error_details))
    print("Did you update the subscription info?")

# import sys
# print('libai-')
# a, b = sys.argv[1], sys.argv[2]   # 接收位置参数
# print(int(a)+int(b))

