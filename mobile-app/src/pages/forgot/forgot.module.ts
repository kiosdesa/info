import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';
import { ForgotPage } from './forgot';

@NgModule({
  declarations: [
    ForgotPage,
  ],
  imports: [
    IonicPageModule.forChild(ForgotPage),
    TranslateModule.forChild()
  ],
  exports: [
    ForgotPage
  ]
})
export class ForgotPageModule {}
