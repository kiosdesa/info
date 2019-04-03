import { NgModule } from '@angular/core';
import { TranslateModule } from '@ngx-translate/core';
import { IonicPageModule } from 'ionic-angular';

import { AddressChoosePage } from './address-choose';

@NgModule({
  declarations: [
    AddressChoosePage,
  ],
  imports: [
    IonicPageModule.forChild(AddressChoosePage),
    TranslateModule.forChild()
  ],
  exports: [
    AddressChoosePage
  ]
})
export class AddressChoosePageModule { }
