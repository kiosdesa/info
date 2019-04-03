import { Platform } from 'ionic-angular';
import { Injectable } from '@angular/core';
import { NativeAudio } from '@ionic-native/native-audio';

export interface TypeAudios
{
  key: any
  asset: any
  type: any
}
type audios = TypeAudios[];

@Injectable()
export class SmartAudioProvider 
{
  audioType: string = 'html5';
  sounds: audios;

  constructor(
    public nativeAudio: NativeAudio,
    public platform: Platform) 
  {
    if(platform.is('cordova'))
    {
        this.audioType = 'native';
    }
  }

  preload(key, asset) 
  {
    if(this.audioType === 'html5')
    {
        let audio = {
            key: key,
            asset: asset,
            type: 'html5'
        };
        this.sounds = [audio];
    } 
    else 
    {
      this.nativeAudio.preloadSimple(key, asset);
      let audio = {
          key: key,
          asset: key,
          type: 'native'
      };
      this.sounds = [audio];
    }
  }

  play(key)
  {
    let audio = this.sounds.find((sound) => {
        //console.log(sound.key)
        if(sound.key == key) return sound.key;
    });

    if(audio.type === 'html5')
    {
        let audioAsset = new Audio(audio.asset);
        audioAsset.play();
    } 
    else 
    {
      this.nativeAudio.play(audio.asset).then((res) => {
          console.log(res);
      }, (err) => {
          console.log(err);
      });
    }
  }
}